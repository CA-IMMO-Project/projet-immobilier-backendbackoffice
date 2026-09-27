<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('reservation')) ?? false;
    }

    public function rules(): array
    {
        return [
            // client_id et terrain_id ne sont jamais réassignés après coup.
            'purchase_request_id' => ['nullable', 'integer', 'exists:purchase_requests,id'],
            'date_reservation' => ['sometimes', 'required', 'date'],
            'date_fin_reservation' => ['nullable', 'date', 'after:date_reservation'],
            'prix_reservation' => ['nullable', 'numeric', 'min:0'],
            'acompte' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(['active', 'expiree', 'annulee', 'transformee_en_vente'])],
        ];
    }
}
