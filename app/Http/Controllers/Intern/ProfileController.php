<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
                \Illuminate\Validation\Rule::unique('users')->ignore($intern->id),
            ],
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $intern->update($data);

        return redirect()->route('intern.profile.edit')->with('success', 'Profil berhasil diperbarui!');
    }
}
