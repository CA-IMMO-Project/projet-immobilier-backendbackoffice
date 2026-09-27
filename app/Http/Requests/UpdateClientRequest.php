<?php

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('client')) ?? false;
    }

    public function rules(): array
    {
        $clientId = $this->route('client')?->id;

        return [
            'nom' => ['sometimes', 'required', 'string', 'max:100'],
            'prenom' => ['sometimes', 'required', 'string', 'max:100'],
            'numero' => ['nullable', 'string', 'max:30'],
            'mail' => ['nullable', 'email', 'max:255', Rule::unique('clients', 'mail')->ignore($clientId)],
            'profession' => ['nullable', 'string', 'max:150'],
            'nationalite' => ['nullable', 'string', 'max:100'],
            'pays_residence' => ['nullable', 'string', 'max:100'],
            'acheteur' => ['sometimes', 'boolean'],
            'proprietaire' => ['sometimes', 'boolean'],
            'date_naissance' => ['nullable','bail','date', 'before:today',
            function ($attribute,$value,$fail){
                if(Carbon::parse($value)->age < 18){
                    $fail('Le client doit être majeur(18 ans ou plus).');
                }
            }],
            'compte_bancaire' => ['sometimes', 'boolean'],
            'piece_identite' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['nouveau', 'actif', 'inactif', 'liste_noire'])],
            // Le mot de passe (accès Espace Client) n'est jamais géré depuis le Back Office.
        ];
    }
}