<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\SearchRequestResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'numero' => $this->numero,
            'mail' => $this->mail,
            'email_verified_at' => $this->email_verified_at,
            'profession' => $this->profession,
            'nationalite' => $this->nationalite,
            'pays_residence' => $this->pays_residence,
            'acheteur' => $this->acheteur,
            'proprietaire' => $this->proprietaire,
            'date_naissance' => $this->date_naissance,
            'compte_bancaire' => $this->compte_bancaire,
            'piece_identite' => $this->piece_identite,
            'a_compte_espace_client' => ! is_null($this->password),
            'status' => $this->status,
            'compteurs' => [
                'terrain_propose' => $this->nombre_terrain_propose,
                'terrain_publie' => $this->nombre_terrain_publie,
                'terrain_vendu' => $this->nombre_terrain_vendu,
                'demande_recherche' => $this->nombre_demande_recherche,
                'demande_achat' => $this->nombre_demande_achat,
                'visite_planifiee' => $this->nombre_visite_planifiee,
                'visite_effectuee' => $this->nombre_visite_effectuee,
                'reservation' => $this->nombre_reservation,
                'transaction' => $this->nombre_transaction,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // password et remember_token ne sont jamais exposés
            'terrains'=>$this->whenLoaded('terrains'),
            'search_requests'=>SearchRequestResource::collection($this->whenLoaded('searchRequests')),
            'purchase_requests'=>$this->whenLoaded('purchaseRequests'),
            'visits'=>$this->whenLoaded('visits'),
            'reservations'=>$this->whenLoaded('reservations'),
        ];
    }
}