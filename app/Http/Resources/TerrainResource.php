<?php

namespace App\Http\Resources;

use App\Http\Resources\ClientResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TerrainResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'titre' => $this->titre,
            'description' => $this->description,
            'superficie' => $this->superficie,
            'prix_m2' => $this->prix_m2,
            'prix_terrain' => $this->prix_terrain,
            'zone' => $this->zone,
            'localisation' => $this->localisation,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'atouts' => $this->atouts,

            'proprietaire_id' => $this->proprietaire_id,
            'proprietaire_nom' => $this->proprietaire_nom,
            'proprietaire_prenom' => $this->proprietaire_prenom,
            'proprietaire_telephone' => $this->proprietaire_telephone,
            'proprietaire_email' => $this->proprietaire_email,
            'proprietaire_pays_residence' => $this->proprietaire_pays_residence,
            'proprietaire_nationalite' => $this->proprietaire_nationalite,
            'proprietaire_profession' => $this->proprietaire_profession,
            'proprietaire_piece_identite' => $this->proprietaire_piece_identite,

            'acheteur_id' => $this->acheteur_id,
            'acheteur_nom' => $this->acheteur_nom,
            'acheteur_prenom' => $this->acheteur_prenom,
            'acheteur_telephone' => $this->acheteur_telephone,
            'acheteur_email' => $this->acheteur_email,
            'acheteur_pays_residence' => $this->acheteur_pays_residence,
            'acheteur_nationalite' => $this->acheteur_nationalite,
            'acheteur_profession' => $this->acheteur_profession,
            'acheteur_piece_identite' => $this->acheteur_piece_identite,

            'title_status' => $this->title_status,
            'status' => $this->status,
            'verified' => $this->verified,
            'verified_at' => $this->verified_at,
            'verified_by' => $this->verified_by,
            'verified_by_name' => $this->verified_by_name,
            'published_at' => $this->published_at,

            'accessibilite' => $this->accessibilite,
            'relief' => $this->relief,
            'paiement_comptant' => $this->paiement_comptant,
            'facilite_paiement' => $this->facilite_paiement,
            'duree_facilite_max' => $this->duree_facilite_max,
            'acompte' => $this->acompte,
            'prix_achat_client' => $this->prix_achat_client,

            'compteurs' => [
                'visite_planifiee' => $this->nombre_visite_planifiee,
                'visite_effectuee' => $this->nombre_visite_effectuee,
                'demande_achat' => $this->nombre_demande_achat,
                'reservation' => $this->nombre_reservation,
                'transaction' => $this->nombre_transaction,
                'client_interesse' => $this->nombre_client_interesse,
            ],

            'media' => $this->media,
            'documents' => $this->documents,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relations : présentes uniquement si chargées via ?include=
            'proprietaire' => new ClientResource($this->whenLoaded('proprietaire')),
            'acheteur' => new ClientResource($this->whenLoaded('acheteur')),
            // TODO: remplacer par leur propre Resource au fur et à mesure qu'elles seront créées
            'purchase_requests' => $this->whenLoaded('purchaseRequests'),
            'visits' => $this->whenLoaded('visits'),
            'reservations' => $this->whenLoaded('reservations'),
            'transactions' => $this->whenLoaded('transactions'),
            'payment_schedules' => $this->whenLoaded('paymentSchedules'),
        ];
    }
}
