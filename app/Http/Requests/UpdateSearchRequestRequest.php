<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSearchRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('search_request')) ?? false;
    }

    public function rules(): array
    {
        return [
            // Le client d'une demande n'est jamais réassigné après coup : on ne modifie pas client_id ici.
            'description' => ['sometimes', 'required', 'string'],
            'zone_recherche' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable','numeric','between:-90,90'],
            'longitude' => ['nullable','numeric','between:-180,180'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'gte:budget_min'],
            'superficie_min' => ['nullable', 'numeric', 'min:0'],
            'superficie_max' => ['nullable', 'numeric', 'min:0', 'gte:superficie_min'],
            'relief' => ['nullable', 'string', 'max:100'],
            'usage' => ['nullable', 'string', 'max:150'],
            'paiement_comptant' => ['sometimes', 'boolean'],
            'facilite_paiement' => ['sometimes', 'boolean'],
            'duree_paiement' => ['nullable', 'integer', 'min:1'],
            'apport' => ['nullable', 'numeric', 'min:0'],
            'infos_supplementaires' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['nouvelle', 'en_traitement', 'proposition_envoyee', 'cloturee', 'annulee'])],
        ];
    }
}
