<?php

namespace App\Http\Requests;

use App\Models\SearchRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSearchRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SearchRequest::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            // description obligatoire, distincte de infos_supplementaires (facultatif) — règle métier du dictionnaire
            'description' => ['required', 'string'],
            'zone_recherche' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable','numeric','between:-90,90'],
            'longitude' => ['nullable','numeric','between:-180,180'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'gte:budget_min'],
            'superficie_min' => ['nullable', 'numeric', 'min:0'],
            'superficie_max' => ['nullable', 'numeric', 'min:0', 'gte:superficie_min'],
            'relief' => ['nullable', 'string', 'max:100'],
            'usage' => ['nullable', 'string', 'max:150'],
            'paiement_comptant' => ['boolean'],
            'facilite_paiement' => ['boolean'],
            'duree_paiement' => ['nullable', 'integer', 'min:1'],
            'apport' => ['nullable', 'numeric', 'min:0'],
            'infos_supplementaires' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['nouvelle', 'en_traitement', 'proposition_envoyee', 'cloturee', 'annulee'])],
            // client_nom, client_prenom, client_telephone, client_email ne sont jamais saisis à la main :
            // ils sont copiés automatiquement depuis le client au moment de la création (voir contrôleur).
        ];
    }
}
