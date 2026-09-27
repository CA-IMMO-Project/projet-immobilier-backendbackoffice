<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitResource extends JsonResource
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

            'purchase_request_id' => $this->purchase_request_id,

            'date_visite' => $this->date_visite,
            'heure_visite' => $this->heure_visite,

            'responsable_id' => $this->responsable_id,
            'responsable_nom' => $this->responsable_nom,
            'responsable_telephone' => $this->responsable_telephone,

            'status' => $this->status,
            'commentaires' => $this->commentaires,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'client' => new ClientResource($this->whenLoaded('client')),
            'terrain' => new TerrainResource($this->whenLoaded('terrain')),
            'purchase_request' => new PurchaseRequestResource($this->whenLoaded('purchaseRequest')),
        ];
    }
}
