<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('visit')) ?? false;
    }

    public function rules(): array
    {
        return [
            // client_id et terrain_id ne sont jamais réassignés après coup.
            'purchase_request_id' => ['nullable', 'integer', 'exists:purchase_requests,id'],
            'date_visite' => ['sometimes', 'required', 'date'],
            'heure_visite' => ['sometimes', 'required', 'date_format:H:i'],
            'responsable_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['sometimes', Rule::in(['demandee', 'a_confirmer', 'confirmee', 'reportee', 'effectuee', 'annulee'])],
            'commentaires' => ['nullable', 'string'],
        ];
    }
}
