<?php
namespace App\Http\Controllers;

use App\Models\{User,CartItem,UserPoint,Reward,RewardClaim,AuthOtpCode};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AuthController extends Controller
{
    public function showLogin(){ return view('pages.auth-login'); }
    public function showRegister(){ return view('pages.auth-register'); }
    public function showForgotPassword(){ return view('pages.auth-forgot-password'); }

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

    public function register(Request $request){
        $data = $request->validate([
            'name'=>'required|string|max:100',
            'email'=>'required|email|max:150|unique:users,email',
            'password'=>'required|string|min:6|confirmed',
        ]);

        $code = $this->createOtp($data['email'], 'register', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        session(['register_verify_email' => $data['email']]);

        $this->sendAuthCodeEmail(
            $data['email'],
            'Kode OTP Verifikasi Registrasi SobatAnak',
            $code,
            'Halo '.$data['name'].', masukkan kode OTP ini untuk menyelesaikan registrasi akun SobatAnak.'
        );

        return redirect()->route('register.verify')->with('success','Mengirim verifikasi ke Gmail anda. Masukkan kode OTP 6 digit untuk mengaktifkan akun.');
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
        if(empty($payload['name']) || empty($payload['email']) || empty($payload['password'])){
            return redirect()->route('register')->withErrors(['code'=>'Data OTP tidak lengkap. Silakan daftar ulang.']);
        }

        if(User::where('email', $payload['email'])->exists()){
            $otp->update(['used_at' => now()]);
            session()->forget('register_verify_email');
            return redirect()->route('login')->withErrors(['email'=>'Email sudah terdaftar. Silakan login.']);
        }

        $user = User::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => $payload['password'],
        ]);

        UserPoint::create(['user_id'=>$user->id,'points'=>1250]);
        $otp->update(['used_at' => now()]);
        session()->forget('register_verify_email');
        session(['user_id'=>$user->id]);

        return redirect()->route('profile')->with('success','Akun berhasil diverifikasi. Selamat datang di SobatAnak!');
    }

    public function resendRegisterCode(){
        $email = session('register_verify_email');
        if(!$email) return redirect()->route('register');

        $latest = AuthOtpCode::where('email', $email)->where('purpose', 'register')->latest()->first();
        $payload = $latest && $latest->payload ? json_decode($latest->payload, true) : null;
        if(!$payload) return redirect()->route('register')->withErrors(['email'=>'Data registrasi tidak ditemukan. Silakan daftar ulang.']);

        $code = $this->createOtp($email, 'register', $payload);

        $this->sendAuthCodeEmail(
            $email,
            'Kode OTP Verifikasi Registrasi SobatAnak',
            $code,
            'Ini kode OTP verifikasi baru untuk akun SobatAnak kamu.'
        );

        return back()->with('success','Kode OTP baru sudah dikirim ke Gmail anda.');
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

        session(['user_id'=>$user->id]);
        return redirect()->route('home')->with('success','Berhasil login.');
    }



    public function rewardsPage()
    {
        $user = session('user_id') ? User::find(session('user_id')) : null;
        if(!$user){
            return redirect()->route('login')->withErrors(['login'=>'Silakan login dulu untuk menukar poin.']);
        }

        $point = UserPoint::firstOrCreate(['user_id'=>$user->id], ['points'=>1250]);
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
        $point = UserPoint::firstOrCreate(['user_id'=>$user->id],['points'=>1250]);
        $cartItems = CartItem::with('product')->where('user_id',$user->id)->latest()->get();
        $claims = RewardClaim::where('user_id',$user->id)->latest()->get();
        return view('pages.profile', compact('user','point','cartItems','claims'));
    }
}
