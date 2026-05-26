<?php

namespace App\Http\Controllers;

use App\Models\LaporanWarga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LaporanWargaController extends Controller
{
    /**
     * GET /laporan
     *
     * Get all laporan warga (for pengurus/admin to review).
     */
    public function index(): JsonResponse
    {
        $laporan = LaporanWarga::with('user:id,nama')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $laporan]);
    }

    /**
     * POST /laporan
     *
     * Warga submits a new report.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'kategori_masalah' => 'required|string|max:255',
            'deskripsi'        => 'required|string',
            'lokasi'           => 'required|string',
            'foto'             => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('laporan_foto', 'public');
        }

        $laporan = LaporanWarga::create([
            'user_id'          => auth()->id(),
            'kategori_masalah' => $request->kategori_masalah,
            'deskripsi'        => $request->deskripsi,
            'lokasi'           => $request->lokasi,
            'foto'             => $fotoPath,
            'status'           => 'DIPROSES',
        ]);

        return response()->json([
            'message' => 'Laporan berhasil dikirim.',
            'data'    => $laporan,
        ], 201);
    }

    /**
     * PATCH /laporan/{id}/status
     *
     * Pengurus / Ketua updates the status of a report.
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:DIPROSES,SELESAI',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $laporan = LaporanWarga::find($id);

        if (!$laporan) {
            return response()->json([
                'message' => 'Laporan tidak ditemukan.',
            ], 404);
        }

        $laporan->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Status laporan berhasil diperbarui.',
            'data'    => $laporan->fresh(),
        ]);
    }

    /**
     * GET /laporan/{user}/riwayat
     *
     * Get report history for a specific user.
     */
    public function riwayat(string $user): JsonResponse
    {
        $targetUser = \App\Models\User::find($user);

        if (!$targetUser) {
            return response()->json([
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $riwayat = LaporanWarga::where('user_id', $user)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Riwayat laporan berhasil diambil.',
            'user'    => [
                'id'   => $targetUser->id,
                'nama' => $targetUser->nama,
            ],
            'data'    => $riwayat,
        ]);
    }
}
