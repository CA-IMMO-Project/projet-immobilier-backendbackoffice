<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSchedule extends Model
{
    use HasFactory;

    protected $table = 'payment_schedules';

    protected $fillable = [
        'transaction_id', 'client_id', 'terrain_id', 'numero_echeance',
        'date_prevue', 'montant', 'montant_paye', 'status', 'date_paiement',
        'commentaire', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'date_prevue' => 'date',
            'montant' => 'decimal:0',
            'montant_paye' => 'decimal:0',
            'date_paiement' => 'date',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function terrain(): BelongsTo
    {
        return $this->belongsTo(Terrain::class, 'terrain_id');
    }

    public function enregistrePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
