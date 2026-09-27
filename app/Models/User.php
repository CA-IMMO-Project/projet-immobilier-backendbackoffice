<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'nom', 'prenom', 'email', 'telephone', 'password',
        'role', 'actif', 'last_login_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'actif' => 'boolean',
        ];
    }

    // Relations

    public function terrainsVerifies(): HasMany
    {
        return $this->hasMany(Terrain::class, 'verified_by');
    }

    public function visitsResponsable(): HasMany
    {
        return $this->hasMany(Visit::class, 'responsable_id');
    }

    public function paymentSchedulesEnregistres(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class, 'recorded_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }
}