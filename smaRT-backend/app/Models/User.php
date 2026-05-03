<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, HasUuids, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_rt',
        'nama',
        'NIK',
        'role',
        'phone',
        'password_hash',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ─── JWT ────────────────────────────────────────────────────

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
            'nama' => $this->nama,
        ];
    }

    // ─── Auth ───────────────────────────────────────────────────

    /**
     * Override the default password column for authentication.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // ─── Relationships ──────────────────────────────────────────

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'id_rt', 'id_rt');
    }

    public function panicLogs(): HasMany
    {
        return $this->hasMany(PanicLog::class, 'user_id', 'id');
    }

    public function pengajuanSurat(): HasMany
    {
        return $this->hasMany(PengajuanSurat::class, 'user_id', 'id');
    }

    public function blockchains(): HasMany
    {
        return $this->hasMany(Blockchain::class, 'bendahara_id', 'id');
    }

    public function broadcasts(): HasMany
    {
        return $this->hasMany(Broadcast::class, 'pengurus_id', 'id');
    }

    public function verifikasiLogs(): HasMany
    {
        return $this->hasMany(VerifikasiLog::class, 'user_id', 'id');
    }
}
