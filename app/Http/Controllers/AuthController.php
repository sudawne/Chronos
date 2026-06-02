<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 1. Hiển thị giao diện Đăng nhập/Đăng ký
    public function showAuthForm()
    {
        // Nếu user đã đăng nhập rồi thì đá thẳng vào trong, không cho ở ngoài màn hình login nữa
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        // Trỏ tới file giao diện (đảm bảo bạn đã lưu giao diện lúc nãy vào resources/views/auth/login.blade.php)
        return view('auth.login'); 
    }

    // 2. Xử lý logic Đăng nhập
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $remember = $request->has('remember'); // Nút "Duy trì đăng nhập"

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'login_error' => 'Email hoặc mật khẩu không chính xác!',
        ])->withInput($request->only('email'));
    }

    // 3. Xử lý logic Đăng ký tài khoản mới
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', // Xác nhận mật khẩu
        ]);

        // Tạo user mới
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Tự động đăng nhập luôn sau khi tạo tài khoản
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Đăng ký tài khoản thành công!');
    }

    // 4. Đăng xuất
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
