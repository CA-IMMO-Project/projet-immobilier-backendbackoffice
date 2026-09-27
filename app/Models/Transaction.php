<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'reference', 'client_id', 'proprietaire_id', 'terrain_id', 'superficie',
        'reservation_id', 'prix_final', 'acompte', 'paiement_comptant',
        'facilite_paiement', 'duree_paiement', 'montant_paye', 'montant_restant',
        'prochaine_echeance', 'status', 'date_finalisation',
    ];

    protected function casts(): array
    {
        return [
            'superficie' => 'decimal:2',
            'prix_final' => 'decimal:0',
            'acompte' => 'decimal:0',
            'montant_paye' => 'decimal:0',
            'montant_restant' => 'decimal:0',
            'paiement_comptant' => 'boolean',
            'facilite_paiement' => 'boolean',
            'prochaine_echeance' => 'date',
            'date_finalisation' => 'datetime',
        ];
    }

    // Acheteur
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    // Vendeur / propriétaire
    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'proprietaire_id');
    }

    public function terrain(): BelongsTo
    {
        return $this->belongsTo(Terrain::class, 'terrain_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class, 'transaction_id');
    }
}
