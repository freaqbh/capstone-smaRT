<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanSurat extends Model
{
    use HasUuids;

    protected $table = 'pengajuan_surat';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'nama_surat',
        'deskripsi_surat',
        'status',
        'dokumen_pendukung',
        'file_final',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
