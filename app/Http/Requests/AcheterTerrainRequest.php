<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcheterTerrainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('terrain')) ?? false;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'prix_final' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
