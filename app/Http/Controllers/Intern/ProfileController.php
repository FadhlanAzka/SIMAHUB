<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $intern = auth()->user();

        return view('intern.profile.edit', compact('intern'));
    }

    public function update(Request $request)
    {
        $intern = auth()->user();

        $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($intern->id),
                'not_regex:/admin|mentor/i',
            ],
            'password' => 'nullable|string|min:8',
        ], [
            'username.required' => 'Username tidak boleh kosong.',
            'username.unique' => 'Username ini sudah digunakan oleh pengguna lain. Silakan pilih username yang berbeda.',
            'username.not_regex' => 'Username Anda tidak diperbolehkan mengandung kata "admin" atau "mentor".',
            'password.min' => 'Kata sandi baru minimal harus 8 karakter.',
        ]);

        $data = [
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $intern->update($data);

        return redirect()->route('intern.profile.edit')->with('success', 'Profil berhasil diperbarui!');
    }
}
