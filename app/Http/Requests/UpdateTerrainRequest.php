<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTerrainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('terrain')) ?? false;
    }

    public function rules(): array
    {
        $terrainId = $this->route('terrain')?->id;

        return [
            'reference' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('terrains', 'reference')->ignore($terrainId)],
            'titre' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'superficie' => ['nullable', 'numeric', 'min:0'],
            'prix_m2' => ['nullable', 'numeric', 'min:0'],
            'prix_terrain' => ['nullable', 'numeric', 'min:0'],
            'zone' => ['nullable', 'string', 'max:150'],
            'localisation' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'atouts' => ['nullable', 'string'],

            // Le propriétaire n'est jamais réassigné après coup depuis cette route :
            // un changement de propriétaire est une décision métier à part, pas un simple champ à éditer.

            'title_status' => ['sometimes', Rule::in(['titre_en_cours', 'titre_foncier', 'certificat_foncier', 'mutation', 'autre'])],
            'status' => ['sometimes', Rule::in(['brouillon', 'en_verification', 'publie', 'reserve', 'vendu', 'retire'])],

            'accessibilite' => ['nullable', 'string'],
            'relief' => ['nullable', 'string', 'max:100'],
            'paiement_comptant' => ['sometimes', 'boolean'],
            'facilite_paiement' => ['sometimes', 'boolean'],
            'duree_facilite_max' => ['nullable', 'integer', 'min:1'],
            'acompte' => ['nullable', 'numeric', 'min:0'],
            'prix_achat_client' => ['nullable', 'numeric', 'min:0'],

            'media' => ['nullable', 'array'],
            'documents' => ['nullable', 'array'],
        ];
    }
}
