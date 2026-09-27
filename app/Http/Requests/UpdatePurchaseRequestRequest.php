<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('purchase_request')) ?? false;
    }

    public function rules(): array
    {
        return [
            // client_id et terrain_id ne sont jamais réassignés après coup.
            'description' => ['sometimes', 'required', 'string'],
            'prix_propose' => ['nullable', 'numeric', 'min:0'],
            'paiement_comptant' => ['sometimes', 'boolean'],
            'facilite_paiement' => ['sometimes', 'boolean'],
            'duree_paiement' => ['nullable', 'integer', 'min:1'],
            'apport' => ['nullable', 'numeric', 'min:0'],
            'infos_supplementaires' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['nouvelle', 'en_etude', 'acceptee', 'rejetee', 'annulee'])],
        ];
    }
}
