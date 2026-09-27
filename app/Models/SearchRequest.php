<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchRequest extends Model
{
    use HasFactory;

    protected $table = 'search_requests';

    protected $fillable = [
        'client_id', 'client_nom','client_prenom','client_telephone','client_email',
        'description', 'zone_recherche','latitude','longitude',
         'budget_min', 'budget_max','superficie_min', 'superficie_max',
          'relief', 'usage','paiement_comptant', 'facilite_paiement', 
          'duree_paiement', 'apport','infos_supplementaires', 'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'budget_min' => 'decimal:0',
            'budget_max' => 'decimal:0',
            'superficie_min' => 'decimal:2',
            'superficie_max' => 'decimal:2',
            'apport' => 'decimal:0',
            'paiement_comptant' => 'boolean',
            'facilite_paiement' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
