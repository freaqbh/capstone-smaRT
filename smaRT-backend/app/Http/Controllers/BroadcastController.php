<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BroadcastController extends Controller
{
    /**
     * GET /broadcast
     *
     * Get broadcast history with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 10);

        $broadcasts = Broadcast::with('pengurus:id,nama')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $broadcasts,
        ]);
    }

    /**
     * POST /broadcast
     *
     * Pengurus / Ketua sends a broadcast announcement to all residents.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'isi_pesan' => 'required|string',
            'kategori' => 'required|in:INFORMASI,DARURAT,KEGIATAN',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $broadcast = Broadcast::create([
            'pengurus_id' => auth()->id(),
            'judul' => $request->judul,
            'isi_pesan' => $request->isi_pesan,
            'kategori' => $request->kategori,
        ]);

        return response()->json([
            'message' => 'Broadcast berhasil dikirim.',
            'data' => $broadcast,
        ], 201);
    }
}
