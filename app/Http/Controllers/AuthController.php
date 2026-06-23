<?php
namespace App\Http\Controllers;

use App\Models\{User,CartItem,UserPoint,Reward,RewardClaim,AuthOtpCode};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuthController extends Controller
{
    public function showLogin(){ return view('pages.auth-login'); }
    public function showRegister(){ return view('pages.auth-register'); }
    public function showForgotPassword(){ return view('pages.auth-forgot-password'); }

    private function userVerificationColumnsReady(): bool
    {
        try {
            return Schema::hasColumn('users', 'status') && Schema::hasColumn('users', 'email_verified_at');
        } catch (Throwable $e) {
            return false;
        }
    }

    private function isPendingUser(User $user): bool
    {
        $hasColumns = $this->userVerificationColumnsReady();

        if ((string)($user->role ?? '') === 'pending') {
            return true;
        }

        if ($hasColumns) {
            return (string)($user->status ?? 'pending') !== 'verified' || empty($user->email_verified_at);
        }

        return false;
    }

    private function markUserVerified(User $user): void
    {
        $user->role = 'user';

        if ($this->userVerificationColumnsReady()) {
            $user->status = 'verified';
            if (empty($user->email_verified_at)) {
                $user->email_verified_at = now();
            }
        }

        $user->save();
        UserPoint::firstOrCreate(['user_id'=>$user->id], ['points'=>0]);
    }

    private function sendAuthCodeEmail(string $email, string $subject, string $code, string $opening): void
    {
        try {
            Mail::raw($opening."\n\nKode OTP SobatAnak kamu: {$code}\n\nKode berlaku 15 menit. Jangan bagikan kode ini ke siapa pun.", function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
        } catch (Throwable $e) {
            session()->flash('mail_warning', 'Kode OTP sudah dibuat, tapi email belum bisa terkirim. Cek konfigurasi MAIL di file .env.');
        }
    }

    private function createOtp(string $email, string $purpose, ?array $payload = null): string
    {
        AuthOtpCode::where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $code = (string) random_int(100000, 999999);

        AuthOtpCode::create([
            'email' => $email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'payload' => $payload ? json_encode($payload) : null,
            'expires_at' => now()->addMinutes(15),
        ]);

        return $code;
    }

    private function findValidOtp(string $email, string $purpose, string $code): ?AuthOtpCode
    {
        $otp = AuthOtpCode::where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if(!$otp || $otp->expires_at->isPast() || !Hash::check($code, $otp->code_hash)){
            return null;
        }

        return $otp;
    }

    private function sendRegisterVerification(User $user, string $message = 'Kode OTP verifikasi baru sudah dikirim ke Gmail anda.'): void
    {
        $code = $this->createOtp($user->email, 'register', [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        session(['register_verify_email' => $user->email]);

        $this->sendAuthCodeEmail(
            $user->email,
            'Kode OTP Verifikasi Registrasi SobatAnak',
            $code,
            'Halo '.$user->name.', masukkan kode OTP ini untuk menyelesaikan verifikasi akun SobatAnak.'
        );

        session()->flash('success', $message);
    }

    public function register(Request $request){
        $data = $request->validate([
            'name'=>'required|string|max:100',
            'email'=>'required|email|max:150|unique:users,email',
            'password'=>'required|string|min:6|confirmed',
        ]);

        $payload = [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            // Akun baru belum boleh jadi user aktif sebelum verifikasi Gmail/OTP.
            'role' => 'pending',
        ];

        if ($this->userVerificationColumnsReady()) {
            $payload['status'] = 'pending';
            $payload['email_verified_at'] = null;
        }

        // Simpan user langsung ke database sebagai pending agar admin tetap bisa melihat pendaftar baru.
        $user = User::create($payload);

        // Semua akun baru mulai dari 0 poin, poin baru naik setelah main game.
        UserPoint::firstOrCreate(['user_id'=>$user->id], ['points'=>0]);

        $this->sendRegisterVerification($user, 'Akun sudah masuk database dengan status Pending. Masukkan OTP Gmail untuk mengaktifkan akun.');

        return redirect()->route('register.verify');
    }

    public function showRegisterVerify(){
        if(!session('register_verify_email')) return redirect()->route('register');
        return view('pages.auth-register-verify');
    }

    public function verifyRegister(Request $request){
        $request->validate(['code'=>'required|string|min:6|max:6']);
        $email = session('register_verify_email');
        if(!$email) return redirect()->route('register')->withErrors(['code'=>'Data registrasi tidak ditemukan. Silakan daftar ulang.']);

        $otp = $this->findValidOtp($email, 'register', (string) $request->code);
        if(!$otp) return back()->withErrors(['code'=>'Kode OTP salah atau sudah kedaluwarsa.'])->withInput();

        $payload = json_decode($otp->payload ?: '{}', true);
        $user = null;

        if(!empty($payload['user_id'])){
            $user = User::find($payload['user_id']);
        }

        if(!$user && !empty($payload['email'])){
            $user = User::where('email', $payload['email'])->first();
        }

        if(!$user){
            return redirect()->route('register')->withErrors(['code'=>'Akun belum tercatat. Silakan daftar ulang.']);
        }

        $this->markUserVerified($user);
        $otp->update(['used_at' => now()]);
        session()->forget('register_verify_email');
        session(['user_id'=>$user->id]);

        return redirect()->route('profile')->with('success','Akun berhasil diverifikasi. Status akun sudah aktif sebagai user SobatAnak.');
    }

    public function resendRegisterCode(){
        $email = session('register_verify_email');
        if(!$email) return redirect()->route('register');

        $user = User::where('email', $email)->first();
        if(!$user) return redirect()->route('register')->withErrors(['email'=>'Akun belum tercatat. Silakan daftar ulang.']);

        $this->sendRegisterVerification($user);

        return back();
    }

    public function sendForgotPassword(Request $request){
        $data = $request->validate(['email'=>'required|email']);
        $user = User::where('email',$data['email'])->first();

        if(!$user){
            return back()->with('success','Jika email terdaftar, kode reset password akan dikirim.');
        }

        $code = $this->createOtp($user->email, 'reset_password');
        session(['password_reset_email' => $user->email]);

        $this->sendAuthCodeEmail(
            $user->email,
            'Kode OTP Reset Password SobatAnak',
            $code,
            'Halo '.$user->name.', masukkan kode OTP ini untuk reset password akun SobatAnak kamu.'
        );

        return redirect()->route('password.code')->with('success','Kode OTP reset password sudah dikirim ke Gmail anda.');
    }

    public function showResetCode(){
        if(!session('password_reset_email')) return redirect()->route('password.request');
        return view('pages.auth-reset-code');
    }

    public function verifyResetCode(Request $request){
        $request->validate(['code'=>'required|string|min:6|max:6']);
        $email = session('password_reset_email');
        if(!$email) return redirect()->route('password.request');

        $otp = $this->findValidOtp($email, 'reset_password', (string) $request->code);
        if(!$otp) return back()->withErrors(['code'=>'Kode OTP reset salah atau sudah kedaluwarsa.'])->withInput();

        session(['password_reset_verified' => true]);
        return redirect()->route('password.new')->with('success','Kode benar. Silakan buat password baru.');
    }

    public function showNewPassword(){
        if(!session('password_reset_email') || !session('password_reset_verified')) return redirect()->route('password.request');
        return view('pages.auth-reset-new-password');
    }

    public function updateNewPassword(Request $request){
        $email = session('password_reset_email');
        if(!$email || !session('password_reset_verified')) return redirect()->route('password.request');

        $data = $request->validate(['password'=>'required|string|min:6|confirmed']);
        $user = User::where('email',$email)->first();
        if(!$user) return redirect()->route('password.request')->withErrors(['email'=>'Akun tidak ditemukan.']);

        $otp = AuthOtpCode::where('email', $email)
            ->where('purpose', 'reset_password')
            ->whereNull('used_at')
            ->latest()
            ->first();

        $user->password = Hash::make($data['password']);
        $user->save();
        if($otp) $otp->update(['used_at' => now()]);
        session()->forget(['password_reset_email','password_reset_verified']);

        return redirect()->route('login')->with('success','Password berhasil diganti. Silakan login dengan password baru.');
    }

    public function login(Request $request){
        $data = $request->validate(['email'=>'required|email','password'=>'required|string']);
        $user = User::where('email',$data['email'])->first();

        if(!$user){
            return back()->withErrors(['email'=>'Email atau password salah.'])->withInput();
        }

        $storedPassword = (string) $user->password;
        $passwordValid = false;

        if(str_starts_with($storedPassword, '$2y$') || str_starts_with($storedPassword, '$2a$') || str_starts_with($storedPassword, '$2b$')){
            $passwordValid = Hash::check($data['password'], $storedPassword);
        } else {
            $passwordValid = hash_equals($storedPassword, $data['password']);
            if($passwordValid){
                $user->password = Hash::make($data['password']);
                $user->save();
            }
        }

        if(!$passwordValid){
            return back()->withErrors(['email'=>'Email atau password salah.'])->withInput();
        }

        // Akun pending tidak boleh masuk. Kirim ulang OTP dan arahkan ke halaman verifikasi Gmail.
        if($this->isPendingUser($user)){
            $this->sendRegisterVerification($user, 'Akun kamu masih Pending. Kami kirim ulang OTP ke Gmail, silakan verifikasi dulu.');
            return redirect()->route('register.verify');
        }

        UserPoint::firstOrCreate(['user_id'=>$user->id], ['points'=>0]);
        session(['user_id'=>$user->id]);
        return redirect()->route('home')->with('success','Berhasil login.');
    }

    public function rewardsPage()
    {
        $user = session('user_id') ? User::find(session('user_id')) : null;
        if(!$user){
            return redirect()->route('login')->withErrors(['login'=>'Silakan login dulu untuk menukar poin.']);
        }

        $point = UserPoint::firstOrCreate(['user_id'=>$user->id], ['points'=>0]);
        $rewards = Reward::orderBy('points')->get();
        $claims = RewardClaim::where('user_id', $user->id)->latest()->get();

        return view('pages.rewards-store', compact('user','point','rewards','claims'));
    }

    public function updateProfile(Request $request)
    {
        $user = session('user_id') ? User::find(session('user_id')) : null;
        if(!$user){
            return redirect()->route('login')->withErrors(['login'=>'Silakan login dulu untuk mengubah profile.']);
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ]);

        $user->name = trim($data['name']);

        if($request->boolean('remove_avatar')){
            if(!empty($user->avatar)){
                $oldPath = public_path($user->avatar);
                if(is_file($oldPath)){
                    @unlink($oldPath);
                }
            }
            $user->avatar = null;
        }

        if($request->hasFile('avatar')){
            if(!empty($user->avatar)){
                $oldPath = public_path($user->avatar);
                if(is_file($oldPath)){
                    @unlink($oldPath);
                }
            }

            $dir = public_path('uploads/profiles');
            if(!is_dir($dir)){
                mkdir($dir, 0775, true);
            }

            $file = $request->file('avatar');
            $filename = 'profile_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $user->avatar = 'uploads/profiles/'.$filename;
        }

        $user->save();

        return back()->with('success', 'Profile berhasil diperbarui.');
    }

    public function logout(){
        session()->forget('user_id');
        return redirect()->route('home')->with('success','Berhasil logout.');
    }

    public function profile(){
        $user = User::find(session('user_id'));
        if(!$user) return redirect()->route('login')->withErrors(['login'=>'Silakan login dulu.']);
        $point = UserPoint::firstOrCreate(['user_id'=>$user->id],['points'=>0]);
        $cartItems = CartItem::with('product')->where('user_id',$user->id)->latest()->get();
        $claims = RewardClaim::where('user_id',$user->id)->latest()->get();
        return view('pages.profile', compact('user','point','cartItems','claims'));
    }
}
