<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    use HasFactory;

    protected $table = 'visits';

    protected $fillable = [
        'client_id', 'client_nom', 'client_prenom', 'client_telephone', 'client_email',
        'terrain_id', 'terrain_titre', 'terrain_localisation', 'terrain_prix',
        'purchase_request_id',
        'date_visite', 'heure_visite',
        'responsable_id', 'responsable_nom', 'responsable_telephone',
        'status', 'commentaires',
    ];

    protected function casts(): array
    {
        return [
            'terrain_prix' => 'decimal:0',
            'date_visite' => 'date',
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

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }
}
