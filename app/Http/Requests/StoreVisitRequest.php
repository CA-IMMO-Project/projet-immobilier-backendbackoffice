<?php

namespace App\Http\Requests;

use App\Models\Visit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Visit::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'terrain_id' => ['required', 'integer', 'exists:terrains,id'],
            'purchase_request_id' => ['nullable', 'integer', 'exists:purchase_requests,id'],
            'date_visite' => ['required', 'date'],
            'heure_visite' => ['required', 'date_format:H:i'],
            'responsable_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', Rule::in(['demandee', 'a_confirmer', 'confirmee', 'reportee', 'effectuee', 'annulee'])],
            'commentaires' => ['nullable', 'string'],
            // client_nom/prenom/telephone/email, terrain_titre/localisation/terrain_prix
            // et responsable_nom sont copiés automatiquement (voir contrôleur).
        ];
    }
}
