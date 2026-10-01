<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8',
        ], [
            'username.required' => 'Username tidak boleh kosong.',
            'username.unique' => 'Username ini sudah digunakan oleh pengguna lain. Silakan pilih username yang berbeda.',
            'password.min' => 'Kata sandi baru minimal harus 8 karakter.',
        ]);

        $data = [
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.profile.edit')->with('success', 'Profil berhasil diperbarui!');
    }
}
