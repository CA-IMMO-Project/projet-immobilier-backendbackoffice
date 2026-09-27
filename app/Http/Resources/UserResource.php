<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'telephone' => $this->telephone,
            'role' => $this->role,
            'actif' => $this->actif,
            'last_login_at' => $this->last_login_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // password et remember_token ne sont jamais exposés (déjà $hidden sur le modèle)
        ];
    }
}
