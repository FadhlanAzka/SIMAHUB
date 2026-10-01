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
        $position = $request->get('position');
        $mentorId = $request->get('mentor_id');

        $query = User::where('role', 'intern');

        if (auth()->user()->role === 'mentor') {
            $query->where('mentor_id', auth()->id());
        } elseif ($mentorId) {
            $query->where('mentor_id', $mentorId);
        }

        if ($position) {
            $query->where('position', $position);
        }

        if ($search) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        $intern = $query->with('mentor')
            ->withCount('attendances')
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        $positions = User::where('role', 'intern')->whereNotNull('position')->where('position', '!=', '')->distinct()->pluck('position');
        
        $mentors = collect();
        if (auth()->user()->role === 'admin') {
            $mentors = User::where('role', 'mentor')->orderBy('name', 'asc')->get();
        }

        return view('admin.intern.index', compact('intern', 'search', 'position', 'mentorId', 'positions', 'mentors'));
    }

    public function create()
    {
        if (auth()->user()->role !== 'admin') abort(403, 'Akses ditolak.');
        $mentors = User::where('role', 'mentor')->orderBy('name', 'asc')->get();
        return view('admin.intern.create', compact('mentors'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403, 'Akses ditolak.');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username', 'not_regex:/admin|mentor/i'],
            'position' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'mentor_id' => ['nullable', 'exists:users,id'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'name.required' => 'Nama peserta magang wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username tersebut sudah terdaftar.',
            'username.not_regex' => 'Username intern tidak boleh mengandung kata "admin" atau "mentor".',
            'mentor_id.exists' => 'Mentor yang dipilih tidak valid.',
            'password.required' => 'Kata sandi wajib diisi (minimal 8 karakter).',
            'password.min' => 'Kata sandi minimal 8 karakter.',
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

        $mentors = User::where('role', 'mentor')->orderBy('name', 'asc')->get();
        return view('admin.intern.edit', compact('intern', 'mentors'));
    }

    public function update(Request $request, User $intern)
    {
        if (auth()->user()->role !== 'admin') abort(403, 'Akses ditolak.');
        if ($intern->role !== 'intern') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($intern->id), 'not_regex:/admin|mentor/i'],
            'position' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'mentor_id' => ['nullable', 'exists:users,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ], [
            'name.required' => 'Nama peserta magang wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'username.not_regex' => 'Username intern tidak boleh mengandung kata "admin" atau "mentor".',
            'mentor_id.exists' => 'Mentor yang dipilih tidak valid.',
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
