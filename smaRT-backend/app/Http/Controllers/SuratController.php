<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SuratController extends Controller
{
    /**
     * GET /surat
     *
     * Get all surat submissions (for pengurus/admin to review).
     */
    public function index(): JsonResponse
    {
        $surat = PengajuanSurat::with('user:id,nama')->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $surat]);
    }

    /**
     * POST /surat/ajukan
     *
     * Warga submits a new letter request.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_surat' => 'required|string|max:255',
            'deskripsi_surat' => 'required|string',
            'dokumen_pendukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $dokumenPath = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $dokumenPath = $request->file('dokumen_pendukung')->store('surat_dokumen', 'public');
        }

        $surat = PengajuanSurat::create([
            'user_id' => auth()->id(),
            'nama_surat' => $request->nama_surat,
            'deskripsi_surat' => $request->deskripsi_surat,
            'status' => 'PENDING',
            'dokumen_pendukung' => $dokumenPath,
        ]);

        return response()->json([
            'message' => 'Surat berhasil diajukan.',
            'data' => $surat,
        ], 201);
    }

    /**
     * PATCH /surat/ajukan
     *
     * Pengurus / Ketua approves or rejects a submitted letter.
     */
    public function review(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|uuid|exists:pengajuan_surat,id',
            'status' => 'required|in:APPROVED,REJECTED',
            'file_final' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $surat = PengajuanSurat::find($request->id);

        if (!$surat) {
            return response()->json([
                'message' => 'Surat dengan ID tersebut tidak ditemukan.',
            ], 404);
        }

        $fileFinalPath = $surat->file_final;
        if ($request->hasFile('file_final')) {
            $fileFinalPath = $request->file('file_final')->store('surat_final', 'public');
        }

        $surat->update([
            'status' => $request->status,
            'file_final' => $fileFinalPath,
        ]);

        return response()->json([
            'message' => 'Status surat berhasil diperbarui.',
            'data' => $surat->fresh(),
        ]);
    }
}
