<?php

namespace App\Http\Controllers;

use App\Models\Blockchain;
use App\Models\VerifikasiLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KasController extends Controller
{
    /**
     * POST /kas/input
     *
     * Bendahara records a new financial transaction.
     * Automatically computes hashchain (SHA-256) linking to previous block.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'jenis_kas' => 'required|in:PEMASUKAN,PENGELUARAN',
            'nominal' => 'required|integer|min:1',
            'keterangan' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request) {
            // Get the last block's hash (genesis block uses all zeros)
            $lastBlock = Blockchain::latest('created_at')->latest('id')->first();
            $previousHash = $lastBlock ? $lastBlock->current_hash : str_repeat('0', 64);

            $data = [
                'bendahara_id' => auth()->id(),
                'jenis_kas' => $request->jenis_kas,
                'nominal' => $request->nominal,
                'keterangan' => $request->keterangan,
            ];

            $currentHash = Blockchain::generateHash($previousHash, $data);

            $block = Blockchain::create([
                'bendahara_id' => $data['bendahara_id'],
                'jenis_kas' => $data['jenis_kas'],
                'nominal' => $data['nominal'],
                'keterangan' => $data['keterangan'],
                'previous_hash' => $previousHash,
                'current_hash' => $currentHash,
            ]);

            return response()->json([
                'message' => 'Transaksi tercatat dan Hash berhasil digenerate.',
                'data' => $block,
            ], 201);
        });
    }

    /**
     * GET /kas/history
     *
     * Get all blockchain transaction history.
     */
    public function index(): JsonResponse
    {
        $blocks = Blockchain::with('bendahara:id,nama')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'data' => $blocks,
        ]);
    }

    /**
     * GET /kas/monitor
     *
     * Verify the integrity of the hashchain.
     * Returns the last block hash and integrity status.
     */
    public function monitor(): JsonResponse
    {
        $blocks = Blockchain::orderBy('created_at', 'asc')->orderBy('id', 'asc')->get();

        if ($blocks->isEmpty()) {
            return response()->json([
                'total_blocks' => 0,
                'total_pemasukan' => 0,
                'total_pengeluaran' => 0,
                'saldo' => 0,
                'last_transaction_id' => null,
                'last_block_hash' => null,
                'status_integritas' => true,
                'server_timestamp' => now()->toIso8601String(),
            ]);
        }

        // Verify the entire chain and compute aggregates
        $isValid = true;
        $previousHash = str_repeat('0', 64);
        $totalPemasukan = 0;
        $totalPengeluaran = 0;
        $invalidBlockId = null;
        $lastRecomputedHash = null;

        foreach ($blocks as $block) {
            // Recompute the hash for this block
            $recomputedHash = Blockchain::generateHash($previousHash, [
                'bendahara_id' => $block->bendahara_id,
                'jenis_kas' => $block->jenis_kas,
                'nominal' => $block->nominal,
                'keterangan' => $block->keterangan,
            ]);

            // Check that the block's previous_hash links correctly
            if ($block->previous_hash !== $previousHash) {
                $isValid = false;
                $invalidBlockId = $block->id;
                $lastRecomputedHash = $recomputedHash;
                break;
            }

            // Check that the stored hash matches the recomputed hash
            if ($block->current_hash !== $recomputedHash) {
                $isValid = false;
                $invalidBlockId = $block->id;
                $lastRecomputedHash = $recomputedHash;
                break;
            }

            // Accumulate totals for ALL blocks
            if ($block->jenis_kas === 'PEMASUKAN') {
                $totalPemasukan += $block->nominal;
            } else {
                $totalPengeluaran += $block->nominal;
            }

            $lastRecomputedHash = $recomputedHash;
            $previousHash = $block->current_hash;
        }

        $lastBlock = $blocks->last();

        // Log the verification result
        VerifikasiLog::create([
            'user_id' => auth()->id(),
            'id_transaksi' => $invalidBlockId ?? $lastBlock->id,
            'jenis_pengecekan' => 'INTEGRITY_CHECK',
            'status' => $isValid ? 'VALID' : 'INVALID',
            'hash_tersimpan' => $invalidBlockId
                ? $blocks->firstWhere('id', $invalidBlockId)->current_hash
                : $lastBlock->current_hash,
            'hash_terhitung' => $lastRecomputedHash,
        ]);

        return response()->json([
            'total_blocks' => $blocks->count(),
            'total_pemasukan' => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran,
            'saldo' => $totalPemasukan - $totalPengeluaran,
            'last_transaction_id' => $lastBlock->id,
            'last_block_hash' => $lastBlock->current_hash,
            'status_integritas' => $isValid,
            'server_timestamp' => now()->toIso8601String(),
        ]);
    }
}
