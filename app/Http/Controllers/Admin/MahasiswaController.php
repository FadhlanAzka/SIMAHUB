<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $mahasiswa = User::where('role', 'mahasiswa')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nim', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('institution', 'like', "%{$search}%")
                      ->orWhere('position', 'like', "%{$search}%");
                });
            })
            ->withCount('attendances')
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.mahasiswa.index', compact('mahasiswa', 'search'));
    }

    public function create()
    {
        return view('admin.mahasiswa.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:50', 'unique:users,nim'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'institution' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama mahasiswa wajib diisi.',
            'nim.required' => 'NIM / Nomor Identitas Magang wajib diisi.',
            'nim.unique' => 'NIM tersebut sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email tersebut sudah terdaftar.',
            'institution.required' => 'Asal perguruan tinggi / instansi wajib diisi.',
            'position.required' => 'Posisi / divisi magang wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi (minimal 6 karakter).',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $validated['role'] = 'mahasiswa';
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', "Mahasiswa magang {$validated['name']} berhasil ditambahkan.");
    }

    public function edit(User $mahasiswa)
    {
        if ($mahasiswa->role !== 'mahasiswa') {
            abort(404);
        }

        return view('admin.mahasiswa.edit', compact('mahasiswa'));
    }

    public function update(Request $request, User $mahasiswa)
    {
        if ($mahasiswa->role !== 'mahasiswa') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($mahasiswa->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($mahasiswa->id)],
            'institution' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama mahasiswa wajib diisi.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.unique' => 'NIM sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'institution.required' => 'Asal perguruan tinggi wajib diisi.',
            'position.required' => 'Posisi / divisi magang wajib diisi.',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $mahasiswa->update($validated);

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', "Data mahasiswa {$mahasiswa->name} berhasil diperbarui.");
    }

    public function destroy(User $mahasiswa)
    {
        if ($mahasiswa->role !== 'mahasiswa') {
            abort(404);
        }

        $name = $mahasiswa->name;
        $mahasiswa->delete();

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', "Mahasiswa {$name} beserta seluruh data absensinya berhasil dihapus.");
    }

    public function resetPassword(User $mahasiswa)
    {
        if ($mahasiswa->role !== 'mahasiswa') {
            abort(404);
        }

        $defaultPassword = 'magang' . ($mahasiswa->nim ?: '123');
        $mahasiswa->update([
            'password' => Hash::make($defaultPassword),
        ]);

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', "Kata sandi {$mahasiswa->name} berhasil direset menjadi: {$defaultPassword}");
    }
}
