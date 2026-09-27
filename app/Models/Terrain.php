<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Terrain extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'terrains';

    protected $fillable = [
        'reference', 'titre', 'description', 'superficie', 'prix_m2', 'prix_terrain',
        'zone', 'localisation', 'latitude', 'longitude', 'atouts',
        'proprietaire_id', 'proprietaire_nom', 'proprietaire_prenom',
        'proprietaire_telephone', 'proprietaire_email', 'proprietaire_pays_residence',
        'proprietaire_nationalite', 'proprietaire_profession', 'proprietaire_piece_identite',
        'acheteur_id', 'acheteur_nom', 'acheteur_prenom',
        'acheteur_telephone', 'acheteur_email', 'acheteur_pays_residence',
        'acheteur_nationalite', 'acheteur_profession', 'acheteur_piece_identite',
        'title_status', 'status',
        'accessibilite', 'relief', 'paiement_comptant', 'facilite_paiement',
        'duree_facilite_max', 'acompte', 'prix_achat_client',
        'media', 'documents',
    ];

    protected function casts(): array
    {
        return [
            'superficie' => 'decimal:2',
            'prix_m2' => 'decimal:0',
            'prix_terrain' => 'decimal:0',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'verified' => 'boolean',
            'verified_at' => 'datetime',
            'published_at' => 'datetime',
            'paiement_comptant' => 'boolean',
            'facilite_paiement' => 'boolean',
            'acompte' => 'decimal:0',
            'prix_achat_client' => 'decimal:0',
            'media' => 'array',
            'documents' => 'array',
        ];
    }

    // Relations

    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'proprietaire_id');
    }

    public function acheteur(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'acheteur_id');
    }

    public function verifiePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'terrain_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'terrain_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'terrain_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'terrain_id');
    }

    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class, 'terrain_id');
    }
}
