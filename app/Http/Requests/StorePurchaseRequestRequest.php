<?php

namespace App\Http\Requests;

use App\Models\PurchaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', PurchaseRequest::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'terrain_id' => ['required', 'integer', 'exists:terrains,id'],
            'description' => ['required', 'string'],
            'prix_propose' => ['nullable', 'numeric', 'min:0'],
            'paiement_comptant' => ['boolean'],
            'facilite_paiement' => ['boolean'],
            'duree_paiement' => ['nullable', 'integer', 'min:1'],
            'apport' => ['nullable', 'numeric', 'min:0'],
            'infos_supplementaires' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['nouvelle', 'en_etude', 'acceptee', 'rejetee', 'annulee'])],
            // client_nom/prenom/telephone/email et terrain_titre/localisation/terrain_prix
            // sont copiés automatiquement (voir contrôleur), jamais saisis à la main.
        ];
    }
}
