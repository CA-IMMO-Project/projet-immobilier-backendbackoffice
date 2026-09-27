<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ClientResource;

class SearchRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            // Copies dénormalisées (contexte au moment de la demande) — voir règle 2 du dictionnaire
            'client_nom' => $this->client_nom,
            'client_prenom' => $this->client_prenom,
            'client_telephone' => $this->client_telephone,
            'client_email' => $this->client_email,

            'description' => $this->description,
            'zone_recherche' => $this->zone_recherche,
            'budget_min' => $this->budget_min,
            'budget_max' => $this->budget_max,
            'superficie_min' => $this->superficie_min,
            'superficie_max' => $this->superficie_max,
            'relief' => $this->relief,
            'usage' => $this->usage,
            'paiement_comptant' => $this->paiement_comptant,
            'facilite_paiement' => $this->facilite_paiement,
            'duree_paiement' => $this->duree_paiement,
            'apport' => $this->apport,
            'infos_supplementaires' => $this->infos_supplementaires,
            'status' => $this->status,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Fiche complète du client actuel, uniquement si demandée via ?include=client
            // (différent des champs client_* ci-dessus, qui sont figés au moment de la demande)
            'client' => new ClientResource($this->whenLoaded('client')),
        ];
    }
}
