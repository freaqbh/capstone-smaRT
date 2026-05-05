<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class WargaController extends Controller
{
    /**
     * GET /warga
     *
     * Retrieve a list of all citizens (Warga) and other users.
     */
    public function index(): JsonResponse
    {
        $warga = User::with('rt')->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $warga]);
    }

    /**
     * DELETE /warga/{id}
     *
     * Delete a citizen by ID.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        // Optional: prevent deleting self
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'Tidak dapat menghapus akun sendiri.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.']);
    }
}
