<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Pastikan user yang request adalah owner.
     * Mengembalikan response 403 jika bukan owner.
     */
    private function checkOwner(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'owner') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya owner yang diizinkan.',
            ], 403);
        }

        return null; // OK, lanjutkan
    }

    /**
     * GET /api/users
     * Ambil semua kasir + statistik
     */
    public function index(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $query = User::with('branch')->where('role', 'kasir');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $users = $query->orderBy('name')->get()
            ->map(fn($u) => $this->formatUser($u));

        $allKasir = User::where('role', 'kasir')->get();

        $branchCounts = Branch::withCount([
            'users' => fn($q) => $q->where('role', 'kasir'),
        ])->get()->mapWithKeys(fn($b) => [(string) $b->id => $b->users_count]);

        return response()->json([
            'users'           => $users,
            'total_users'     => $allKasir->count(),
            'active_cashiers' => $allKasir->where('status', 'aktif')->count(),
            'branch_counts'   => $branchCounts,
            'branches'        => Branch::select('id', 'name')->get(),
        ]);
    }

    /**
     * POST /api/users
     * Tambah kasir baru
     */
    public function store(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'branch_id' => 'nullable|exists:branches,id',
            'status'    => 'in:aktif,nonaktif',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'kasir',
            'branch_id' => $request->branch_id ?: null,
            'status'    => $request->status ?? 'aktif',
        ]);

        $user->load('branch');

        return response()->json([
            'message' => 'Kasir berhasil ditambahkan.',
            'user'    => $this->formatUser($user),
        ], 201);
    }

    /**
     * GET /api/users/{user}
     */
    public function show(Request $request, User $user)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        if ($user->role !== 'kasir') {
            return response()->json(['message' => 'User bukan kasir.'], 403);
        }

        $user->load('branch');
        return response()->json(['user' => $this->formatUser($user)]);
    }

    /**
     * PUT /api/users/{user}
     */
    public function update(Request $request, User $user)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        if ($user->role !== 'kasir') {
            return response()->json(['message' => 'User bukan kasir.'], 403);
        }

        $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'branch_id' => 'nullable|exists:branches,id',
            'status'    => 'in:aktif,nonaktif',
        ]);

        $user->update([
            'name'      => $request->name,
            'email'     => $request->email,
            'branch_id' => $request->branch_id ?: null,
            'status'    => $request->status ?? $user->status,
        ]);

        $user->load('branch');

        return response()->json([
            'message' => 'Data kasir berhasil diperbarui.',
            'user'    => $this->formatUser($user),
        ]);
    }

    /**
     * POST /api/users/{user}/reset-password
     */
    public function resetPassword(Request $request, User $user)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        if ($user->role !== 'kasir') {
            return response()->json(['message' => 'User bukan kasir.'], 403);
        }

        $request->validate([
            'password'              => 'required|string|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'message' => "Password untuk {$user->name} berhasil direset.",
        ]);
    }

    /**
     * DELETE /api/users/{user}
     */
    public function destroy(Request $request, User $user)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        if ($user->role !== 'kasir') {
            return response()->json(['message' => 'User bukan kasir.'], 403);
        }

        $name = $user->name;
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => "{$name} berhasil dihapus."]);
    }

    /**
     * Format user untuk response JSON
     */
    private function formatUser(User $user): array
    {
        return [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->role,
            'branch_id'   => $user->branch_id,
            'branch_name' => $user->branch?->name ?? '-',
            'status'      => $user->status,
            'is_active'   => $user->status === 'aktif',
            'created_at'  => $user->created_at?->format('d/m/Y'),
        ];
    }
}
