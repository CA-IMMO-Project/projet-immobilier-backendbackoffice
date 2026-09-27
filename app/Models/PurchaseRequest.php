<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $table = 'purchase_requests';

    protected $fillable = [
        'client_id', 'client_nom', 'client_prenom', 'client_telephone', 'client_email',
        'terrain_id', 'terrain_titre', 'terrain_localisation', 'terrain_prix',
        'description', 'prix_propose',
        'paiement_comptant', 'facilite_paiement', 'duree_paiement', 'apport',
        'infos_supplementaires', 'status',
    ];

    protected function casts(): array
    {
        return [
            'terrain_prix' => 'decimal:0',
            'prix_propose' => 'decimal:0',
            'apport' => 'decimal:0',
            'paiement_comptant' => 'boolean',
            'facilite_paiement' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function terrain(): BelongsTo
    {
        return $this->belongsTo(Terrain::class, 'terrain_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'purchase_request_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'purchase_request_id');
    }
}
