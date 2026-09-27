<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client_nom' => $this->client_nom,
            'client_prenom' => $this->client_prenom,
            'client_telephone' => $this->client_telephone,
            'client_email' => $this->client_email,

            'terrain_id' => $this->terrain_id,
            'terrain_titre' => $this->terrain_titre,
            'terrain_localisation' => $this->terrain_localisation,
            'terrain_prix' => $this->terrain_prix,

            'description' => $this->description,
            'prix_propose' => $this->prix_propose,
            'paiement_comptant' => $this->paiement_comptant,
            'facilite_paiement' => $this->facilite_paiement,
            'duree_paiement' => $this->duree_paiement,
            'apport' => $this->apport,
            'infos_supplementaires' => $this->infos_supplementaires,
            'status' => $this->status,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'client' => new ClientResource($this->whenLoaded('client')),
            'terrain' => new TerrainResource($this->whenLoaded('terrain')),
            'visits' => $this->whenLoaded('visits'),
            'reservations' => $this->whenLoaded('reservations'),
        ];
    }
}
