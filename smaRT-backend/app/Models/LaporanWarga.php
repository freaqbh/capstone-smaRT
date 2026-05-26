<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanWarga extends Model
{
    use HasUuids;

    protected $table = 'laporan_warga';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'kategori_masalah',
        'deskripsi',
        'lokasi',
        'foto',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
