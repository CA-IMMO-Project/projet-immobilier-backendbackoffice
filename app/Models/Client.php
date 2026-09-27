<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Client extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $table = 'clients';

    protected $fillable = [
        'nom', 'prenom', 'numero', 'mail', 'profession', 'nationalite',
        'pays_residence', 'acheteur', 'proprietaire', 'date_naissance',
        'compte_bancaire', 'piece_identite', 'password', 'status',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_naissance' => 'date',
            'acheteur' => 'boolean',
            'proprietaire' => 'boolean',
            'compte_bancaire' => 'boolean',
            'password' => 'hashed',
        ];
    }

    // Relations

    public function terrains(): HasMany
    {
        return $this->hasMany(Terrain::class, 'proprietaire_id');
    }

    public function searchRequests(): HasMany
    {
        return $this->hasMany(SearchRequest::class, 'client_id');
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'client_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'client_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'client_id');
    }

    public function achats(): HasMany
    {
        return $this->hasMany(Transaction::class, 'client_id');
    }

    public function ventes(): HasMany
    {
        return $this->hasMany(Transaction::class, 'proprietaire_id');
    }
}
