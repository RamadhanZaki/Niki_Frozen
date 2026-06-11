<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BranchController extends Controller
{
    private function checkOwner(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'owner') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        return null;
    }

    /** GET /api/branches */
    public function index(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        $branches = Branch::withCount(['users', 'products'])
            ->orderBy('name')
            ->get()
            ->map(function ($b) {
                return [
                    'id'            => $b->id,
                    'name'          => $b->name,
                    'address'       => $b->address,
                    'phone'         => $b->phone,
                    'users_count'   => $b->users_count,
                    'products_count'=> $b->products_count,
                    'created_at'    => $b->created_at?->format('Y-m-d'),
                ];
            });

        return response()->json($branches);
    }

    /** POST /api/branches */
    public function store(Request $request)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        try {
            $data = $request->validate([
                'name'    => 'required|string|max:100|unique:branches,name',
                'address' => 'nullable|string|max:255',
                'phone'   => 'nullable|string|max:20',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        $branch = Branch::create($data);

        return response()->json([
            'message' => 'Cabang berhasil ditambahkan.',
            'branch'  => $branch,
        ], 201);
    }

    /** PUT /api/branches/{branch} */
    public function update(Request $request, Branch $branch)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        try {
            $data = $request->validate([
                'name'    => 'required|string|max:100|unique:branches,name,' . $branch->id,
                'address' => 'nullable|string|max:255',
                'phone'   => 'nullable|string|max:20',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        $branch->update($data);

        return response()->json([
            'message' => 'Cabang berhasil diperbarui.',
            'branch'  => $branch,
        ]);
    }

    /** DELETE /api/branches/{branch} */
    public function destroy(Request $request, Branch $branch)
    {
        $deny = $this->checkOwner($request);
        if ($deny) return $deny;

        if ($branch->users()->count() > 0) {
            return response()->json([
                'message' => 'Tidak bisa menghapus cabang yang masih memiliki pengguna aktif.',
            ], 422);
        }

        $branch->delete();

        return response()->json(['message' => 'Cabang berhasil dihapus.']);
    }
}
