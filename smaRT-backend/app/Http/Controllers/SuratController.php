<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SuratController extends Controller
{
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
            'dokumen_pendukung' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $surat = PengajuanSurat::create([
            'user_id' => auth()->id(),
            'nama_surat' => $request->nama_surat,
            'deskripsi_surat' => $request->deskripsi_surat,
            'status' => 'PENDING',
            'dokumen_pendukung' => $request->dokumen_pendukung,
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
            'file_final' => 'nullable|string',
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

        $surat->update([
            'status' => $request->status,
            'file_final' => $request->file_final,
        ]);

        return response()->json([
            'message' => 'Status surat berhasil diperbarui.',
            'data' => $surat->fresh(),
        ]);
    }
}
