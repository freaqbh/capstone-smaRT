<?php

namespace App\Http\Controllers;

use App\Models\Blockchain;
use App\Models\PengajuanSurat;
use App\Models\User;
use App\Models\PanicLog;
use App\Models\Broadcast;
use Illuminate\Http\JsonResponse;

class LogController extends Controller
{
    /**
     * GET /log
     *
     * Returns an aggregated list of recent system activities.
     */
    public function index(): JsonResponse
    {
        $activities = collect();
        $limit = 20;

        // Ambil panic log
        PanicLog::with('user:id,nama')->latest('created_at')->take($limit)->get()->each(function ($log) use ($activities) {
            $activities->push([
                'id' => 'panic_' . $log->id,
                'icon' => '🚨', 'color' => 'red',
                'title' => 'SOS ditekan oleh', 'tag' => $log->user->nama ?? 'Warga',
                'desc' => 'Lokasi: ' . substr($log->latitude, 0, 8) . ', ' . substr($log->longitude, 0, 8),
                'created_at' => $log->created_at,
            ]);
        });

        // Ambil pendaftaran warga baru
        User::latest('created_at')->take($limit)->get()->each(function ($u) use ($activities) {
            $activities->push([
                'id' => 'user_' . $u->id,
                'icon' => '👤', 'color' => 'purple',
                'title' => 'User baru terdaftar', 'tag' => $u->nama,
                'desc' => 'Peran: ' . $u->role,
                'created_at' => $u->created_at,
            ]);
        });

        // Ambil pengajuan surat
        PengajuanSurat::with('user:id,nama')->latest('created_at')->take($limit)->get()->each(function ($s) use ($activities) {
            $activities->push([
                'id' => 'surat_' . $s->id,
                'icon' => '📧', 'color' => 'blue',
                'title' => 'Pengajuan surat —', 'tag' => substr($s->nama_surat, 0, 25),
                'desc' => ($s->user->nama ?? 'Warga') . ' · Status: ' . $s->status,
                'created_at' => $s->created_at,
            ]);
        });

        // Ambil transaksi kas terbaru
        Blockchain::with('bendahara:id,nama')->latest('created_at')->take($limit)->get()->each(function ($b) use ($activities) {
            $activities->push([
                'id' => 'kas_' . $b->id,
                'icon' => '✅', 'color' => 'green',
                'title' => 'Transaksi kas', 'tag' => ($b->jenis_kas == 'PEMASUKAN' ? '+' : '-') . 'Rp ' . number_format($b->nominal, 0, ',', '.'),
                'desc' => 'Pencatat: ' . ($b->bendahara->nama ?? 'Sistem') . ' - ' . substr($b->keterangan, 0, 50),
                'created_at' => $b->created_at,
            ]);
        });

        // Ambil broadcast
        Broadcast::with('pengurus:id,nama')->latest('created_at')->take($limit)->get()->each(function ($b) use ($activities) {
            $activities->push([
                'id' => 'broadcast_' . $b->id,
                'icon' => '📢', 'color' => 'orange',
                'title' => 'Broadcast dibuat —', 'tag' => $b->kategori,
                'desc' => $b->judul,
                'created_at' => $b->created_at,
            ]);
        });

        // Urutkan semua aktivitas dan ambil 50 terbaru
        $recentActivities = $activities->sortByDesc('created_at')->take(50)->values();

        return response()->json([
            'data' => $recentActivities
        ], 200);
    }
}
