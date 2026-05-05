<?php

namespace App\Http\Controllers;

use App\Models\Blockchain;
use App\Models\PengajuanSurat;
use App\Models\User;
use App\Models\PanicLog;
use App\Models\Broadcast;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * GET /dashboard
     *
     * Returns aggregate data needed for the admin dashboard.
     * Includes statistics and all blockchain blocks.
     */
    public function index(): JsonResponse
    {
        // 1. Statistics
        $totalWarga = User::where('role', 'WARGA')->count();
        $totalPengurus = User::whereIn('role', ['PENGURUS', 'KETUA', 'BENDAHARA'])->count();
        
        $suratStats = [
            'total' => PengajuanSurat::count(),
            'pending' => PengajuanSurat::where('status', 'PENDING')->count(),
            'diproses' => PengajuanSurat::where('status', 'DIPROSES')->count(),
            'selesai' => PengajuanSurat::where('status', 'SELESAI')->count(),
            'ditolak' => PengajuanSurat::where('status', 'DITOLAK')->count(),
        ];

        // 2. Kas / Blockchain Summary
        $blocks = Blockchain::with('bendahara:id,nama')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();
            
        $pemasukan = $blocks->where('jenis_kas', 'PEMASUKAN')->sum('nominal');
        $pengeluaran = $blocks->where('jenis_kas', 'PENGELUARAN')->sum('nominal');
        $saldo = $pemasukan - $pengeluaran;

        $kasSummary = [
            'total_pemasukan' => $pemasukan,
            'total_pengeluaran' => $pengeluaran,
            'saldo' => $saldo,
            'total_blocks' => $blocks->count(),
        ];

        // 3. Log Aktivitas Sistem (Aggregated)
        $activities = collect();

        // Ambil panic log
        PanicLog::with('user:id,nama')->latest('created_at')->take(5)->get()->each(function ($log) use ($activities) {
            $activities->push([
                'icon' => '🚨', 'color' => 'red',
                'title' => 'SOS ditekan oleh', 'tag' => $log->user->nama ?? 'Warga',
                'desc' => 'Lokasi: ' . substr($log->latitude, 0, 7) . ',' . substr($log->longitude, 0, 7),
                'created_at' => $log->created_at,
            ]);
        });

        // Ambil pendaftaran warga baru
        User::latest('created_at')->take(5)->get()->each(function ($u) use ($activities) {
            $activities->push([
                'icon' => '👤', 'color' => 'purple',
                'title' => 'User baru terdaftar', 'tag' => $u->nama,
                'desc' => 'Peran: ' . $u->role,
                'created_at' => $u->created_at,
            ]);
        });

        // Ambil pengajuan surat
        PengajuanSurat::with('user:id,nama')->latest('created_at')->take(5)->get()->each(function ($s) use ($activities) {
            $activities->push([
                'icon' => '📧', 'color' => 'blue',
                'title' => 'Surat baru diajukan —', 'tag' => substr($s->nama_surat, 0, 20),
                'desc' => ($s->user->nama ?? 'Warga') . ' · Status: ' . $s->status,
                'created_at' => $s->created_at,
            ]);
        });

        // Ambil transaksi kas terbaru
        $blocks->sortByDesc('created_at')->take(5)->each(function ($b) use ($activities) {
            $activities->push([
                'icon' => '✅', 'color' => 'green',
                'title' => 'Transaksi kas', 'tag' => ($b->jenis_kas == 'PEMASUKAN' ? '+' : '-') . 'Rp ' . number_format($b->nominal, 0, ',', '.'),
                'desc' => 'Bendahara: ' . ($b->bendahara->nama ?? 'Sistem'),
                'created_at' => $b->created_at,
            ]);
        });

        // Ambil broadcast
        Broadcast::with('pengurus:id,nama')->latest('created_at')->take(5)->get()->each(function ($b) use ($activities) {
            $activities->push([
                'icon' => '📢', 'color' => 'orange',
                'title' => 'Broadcast dibuat —', 'tag' => $b->kategori,
                'desc' => $b->judul,
                'created_at' => $b->created_at,
            ]);
        });

        // Urutkan semua aktivitas dan ambil 10 terbaru
        $recentActivities = $activities->sortByDesc('created_at')->take(10)->values();

        // 4. Data untuk komponen Dashboard lainnya
        $recentSurat = PengajuanSurat::with('user:id,nama')
            ->latest('created_at')->take(5)->get();
            
        $recentAgenda = Broadcast::where('kategori', 'KEGIATAN')
            ->latest('created_at')->take(5)->get();
            
        $laporanWarga = PanicLog::with('user:id,nama')
            ->latest('created_at')->take(5)->get();

        return response()->json([
            'message' => 'Dashboard data retrieved successfully',
            'data' => [
                'users' => [
                    'warga' => $totalWarga,
                    'pengurus' => $totalPengurus,
                    'total' => $totalWarga + $totalPengurus,
                ],
                'surat' => $suratStats,
                'kas_summary' => $kasSummary,
                'recent_activities' => $recentActivities,
                'recent_surat' => $recentSurat,
                'recent_agenda' => $recentAgenda,
                'laporan_warga' => $laporanWarga,
                'blocks' => $blocks, // Mengembalikan semua block untuk ditampilkan di dashboard
            ]
        ], 200);
    }
}
