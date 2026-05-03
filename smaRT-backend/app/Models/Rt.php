<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rt extends Model
{
    use HasUuids;

    protected $table = 'rt';
    protected $primaryKey = 'id_rt';
    public $timestamps = false;

    protected $fillable = [
        'nama_rt',
        'alamat',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get all users belonging to this RT.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_rt', 'id_rt');
    }
}
