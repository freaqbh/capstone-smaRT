<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Services\FcmNotificationService;
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
    public function store(Request $request, FcmNotificationService $fcm): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'judul'             => 'required|string|max:255',
            'isi_pesan'         => 'required|string',
            'kategori'          => 'required|in:INFORMASI,DARURAT,KEGIATAN',
            'tanggal_kegiatan'  => 'nullable|required_if:kategori,KEGIATAN|date',
            'waktu_kegiatan'    => 'nullable|required_if:kategori,KEGIATAN|string|max:100',
            'lokasi'            => 'nullable|required_if:kategori,KEGIATAN|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $broadcast = Broadcast::create([
            'pengurus_id'      => auth()->id(),
            'judul'            => $request->judul,
            'isi_pesan'        => $request->isi_pesan,
            'kategori'         => $request->kategori,
            'tanggal_kegiatan' => $request->tanggal_kegiatan,
            'waktu_kegiatan'   => $request->waktu_kegiatan,
            'lokasi'           => $request->lokasi,
        ]);

        // Kirim push notification ke semua warga di RT
        $rtId = auth()->user()->id_rt;
        $fcm->sendToRT($rtId, $broadcast->judul, $broadcast->isi_pesan, [
            'type'         => 'broadcast',
            'broadcast_id' => $broadcast->id,
        ]);

        return response()->json([
            'message' => 'Broadcast berhasil dikirim.',
            'data' => $broadcast,
        ], 201);
    }
}
