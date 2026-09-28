<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login yang bersih dan profesional.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Memproses autentikasi login (bisa menggunakan Email atau NIM).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $loginInput = $credentials['login'];
        $password = $credentials['password'];
        $remember = $request->boolean('remember');

        if (Auth::attempt(['username' => $loginInput, 'password' => $password], $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            return redirect()->intended(route('dashboard'))
                ->with('login_success', 'Selamat datang, ' . $user->name . '!');
        }

        return back()->withErrors([
            'login' => 'Username atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('login');
    }

    /**
     * Memproses logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }
}
