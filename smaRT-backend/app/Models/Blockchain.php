<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blockchain extends Model
{
    use HasUuids;

    protected $table = 'blockchain';
    public $timestamps = false;

    protected $fillable = [
        'bendahara_id',
        'jenis_kas',
        'nominal',
        'keterangan',
        'previous_hash',
        'current_hash',
    ];

    protected $casts = [
        'nominal' => 'integer',
        'created_at' => 'datetime',
    ];

    public function bendahara(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bendahara_id', 'id');
    }

    /**
     * Generate SHA-256 hash for the current block.
     *
     * Hash is computed from deterministic data only (no timestamp)
     * so that verification can reliably recompute it from stored fields.
     */
    public static function generateHash(string $previousHash, array $data): string
    {
        $payload = $previousHash
            . $data['bendahara_id']
            . $data['jenis_kas']
            . $data['nominal']
            . $data['keterangan'];

        return hash('sha256', $payload);
    }
}
