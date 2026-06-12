<?php
namespace App\Http\Controllers;
use App\Models\{User,CartItem,UserPoint,RewardClaim};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin(){ return view('pages.auth-login'); }
    public function showRegister(){ return view('pages.auth-register'); }

    public function register(Request $request){
        $data = $request->validate([
            'name'=>'required|string|max:100',
            'email'=>'required|email|max:150|unique:users,email',
            'password'=>'required|string|min:6|confirmed',
        ]);
        $user = User::create([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'password'=>Hash::make($data['password']),
        ]);
        UserPoint::create(['user_id'=>$user->id,'points'=>1250]);
        session(['user_id'=>$user->id]);
        return redirect()->route('profile')->with('success','Akun berhasil dibuat. Selamat datang di SobatAnak!');
    }

    public function login(Request $request){
        $data = $request->validate(['email'=>'required|email','password'=>'required|string']);
        $user = User::where('email',$data['email'])->first();

        if(!$user){
            return back()->withErrors(['email'=>'Email atau password salah.'])->withInput();
        }

        $storedPassword = (string) $user->password;
        $passwordValid = false;

        // Normal Laravel password: bcrypt/hash.
        if(str_starts_with($storedPassword, '$2y$') || str_starts_with($storedPassword, '$2a$') || str_starts_with($storedPassword, '$2b$')){
            $passwordValid = Hash::check($data['password'], $storedPassword);
        } else {
            // Fallback untuk database lama yang password-nya masih plain text.
            $passwordValid = hash_equals($storedPassword, $data['password']);

            // Kalau berhasil login dari password lama, langsung ubah ke bcrypt agar aman dan tidak error lagi.
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
