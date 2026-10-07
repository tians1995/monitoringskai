<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Users/Index', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email', 'role', 'created_at']),
            'currentUserId' => auth()->id(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|in:ADMIN,AUDITOR',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => 'required|in:ADMIN,AUDITOR',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($user->id === auth()->id() && $data['role'] !== 'ADMIN') {
            abort(403, 'Admin tidak dapat mengubah perannya sendiri.');
        }

        if ($user->role === 'ADMIN' && $data['role'] !== 'ADMIN' && User::where('role', 'ADMIN')->count() <= 1) {
            abort(422, 'Setidaknya harus ada satu akun admin.');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Admin tidak dapat menghapus akunnya sendiri.');

        if ($user->role === 'ADMIN' && User::where('role', 'ADMIN')->count() <= 1) {
            abort(422, 'Admin terakhir tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
