<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifikasiLog extends Model
{
    use HasUuids;

    protected $table = 'verifikasi_log';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'id_transaksi',
        'jenis_pengecekan',
        'status',
        'hash_tersimpan',
        'hash_terhitung',
    ];

    protected $casts = [
        'waktu_cek' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
