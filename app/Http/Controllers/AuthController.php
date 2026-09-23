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
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('mahasiswa.dashboard');
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
            'login.required' => 'Email atau NIM wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $loginInput = $credentials['login'];
        $password = $credentials['password'];
        $remember = $request->boolean('remember');

        // Deteksi apakah input login adalah format email atau NIM
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'nim';

        if (Auth::attempt([$fieldType => $loginInput, 'password' => $password], $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Selamat datang kembali, ' . $user->name);
            }

            return redirect()->intended(route('mahasiswa.dashboard'))
                ->with('success', 'Selamat datang, ' . $user->name);
        }

        return back()->withErrors([
            'login' => 'Email/NIM atau kata sandi yang Anda masukkan salah.',
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
