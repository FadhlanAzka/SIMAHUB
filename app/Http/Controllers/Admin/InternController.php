<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class internController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $intern = User::where('role', 'intern')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('position', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->withCount('attendances')
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.intern.index', compact('intern', 'search'));
    }

    public function create()
    {
        if (auth()->user()->role !== 'admin') abort(403, 'Akses ditolak.');
        return view('admin.intern.create');
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403, 'Akses ditolak.');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'position' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama peserta magang wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username tersebut sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi (minimal 6 karakter).',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $validated['role'] = 'intern';
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.intern.index')
            ->with('success', "Peserta magang {$validated['name']} berhasil ditambahkan.");
    }

    public function edit(User $intern)
    {
        if (auth()->user()->role !== 'admin') abort(403, 'Akses ditolak.');
        if ($intern->role !== 'intern') {
            abort(404);
        }

        return view('admin.intern.edit', compact('intern'));
    }

    public function update(Request $request, User $intern)
    {
        if (auth()->user()->role !== 'admin') abort(403, 'Akses ditolak.');
        if ($intern->role !== 'intern') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($intern->id)],
            'position' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama peserta magang wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $intern->update($validated);

        return redirect()->route('admin.intern.index')
            ->with('success', "Data peserta magang {$intern->name} berhasil diperbarui.");
    }

    public function destroy(User $intern)
    {
        if (auth()->user()->role !== 'admin') abort(403, 'Akses ditolak.');
        if ($intern->role !== 'intern') {
            abort(404);
        }

        $name = $intern->name;
        $intern->delete();

        return redirect()->route('admin.intern.index')
            ->with('success', "Peserta magang {$name} beserta seluruh data absensinya berhasil dihapus.");
    }

    public function resetPassword(User $intern)
    {
        if (auth()->user()->role !== 'admin') abort(403, 'Akses ditolak.');
        if ($intern->role !== 'intern') {
            abort(404);
        }

        $defaultPassword = 'password';
        $intern->update([
            'password' => Hash::make($defaultPassword),
        ]);

        return redirect()->route('admin.intern.index')
            ->with('success', "Kata sandi peserta magang {$intern->name} berhasil direset menjadi: {$defaultPassword}");
    }
}
