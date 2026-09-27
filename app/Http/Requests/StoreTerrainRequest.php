<?php

namespace App\Http\Requests;

use App\Models\Terrain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTerrainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Terrain::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'reference' => ['required', 'string', 'max:50', 'unique:terrains,reference'],
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'superficie' => ['nullable', 'numeric', 'min:0'],
            'prix_m2' => ['nullable', 'numeric', 'min:0'],
            'prix_terrain' => ['nullable', 'numeric', 'min:0'],
            'zone' => ['nullable', 'string', 'max:150'],
            'localisation' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'atouts' => ['nullable', 'string'],

            'proprietaire_id' => ['required', 'integer', 'exists:clients,id'],
            // proprietaire_nom/prenom/telephone/email/pays_residence/nationalite/profession/piece_identite
            // sont copiés automatiquement depuis le client au moment de la création (voir contrôleur).

            'title_status' => ['nullable', Rule::in(['titre_en_cours', 'titre_foncier', 'certificat_foncier', 'mutation', 'autre'])],
            'status' => ['nullable', Rule::in(['brouillon', 'en_verification', 'publie', 'reserve', 'vendu', 'retire'])],

            'accessibilite' => ['nullable', 'string'],
            'relief' => ['nullable', 'string', 'max:100'],
            'paiement_comptant' => ['boolean'],
            'facilite_paiement' => ['boolean'],
            'duree_facilite_max' => ['nullable', 'integer', 'min:1'],
            'acompte' => ['nullable', 'numeric', 'min:0'],
            'prix_achat_client' => ['nullable', 'numeric', 'min:0'],

            'media' => ['nullable', 'array'],
            'documents' => ['nullable', 'array'],
        ];
    }
}
