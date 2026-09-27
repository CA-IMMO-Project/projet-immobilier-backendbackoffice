<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    protected $fillable = [
        'client_id', 'client_nom', 'client_prenom', 'client_telephone', 'client_email',
        'terrain_id', 'terrain_titre', 'terrain_localisation', 'terrain_prix',
        'purchase_request_id',
        'date_reservation', 'date_fin_reservation', 'prix_reservation',
        'acompte', 'status',
    ];

    protected function casts(): array
    {
        return [
            'terrain_prix' => 'decimal:0',
            'prix_reservation' => 'decimal:0',
            'acompte' => 'decimal:0',
            'date_reservation' => 'datetime',
            'date_fin_reservation' => 'datetime',
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

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'reservation_id');
    }
}
