<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Broadcast extends Model
{
    use HasUuids;

    protected $table = 'broadcast';
    public $timestamps = false;

    protected $fillable = [
        'pengurus_id',
        'judul',
        'isi_pesan',
        'kategori',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function pengurus(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengurus_id', 'id');
    }
}
