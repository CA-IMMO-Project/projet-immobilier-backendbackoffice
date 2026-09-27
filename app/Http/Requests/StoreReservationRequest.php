<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Reservation::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'terrain_id' => ['required', 'integer', 'exists:terrains,id'],
            'purchase_request_id' => ['nullable', 'integer', 'exists:purchase_requests,id'],
            'date_reservation' => ['required', 'date'],
            'date_fin_reservation' => ['nullable', 'date', 'after:date_reservation'],
            'prix_reservation' => ['nullable', 'numeric', 'min:0'],
            'acompte' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(['active', 'expiree', 'annulee', 'transformee_en_vente'])],
            // client_nom/prenom/telephone/email et terrain_titre/localisation/terrain_prix
            // sont copiés automatiquement (voir contrôleur).
        ];
    }
}
