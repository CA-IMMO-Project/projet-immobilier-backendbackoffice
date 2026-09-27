<?php

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Client::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'numero' => ['nullable', 'string', 'min:10','max:30'],
            'mail' => ['nullable', 'email', 'max:255', 'unique:clients,mail'],
            'profession' => ['nullable', 'string', 'max:150'],
            'nationalite' => ['nullable', 'string', 'max:100'],
            'pays_residence' => ['nullable', 'string', 'max:100'],
            'acheteur' => ['boolean'],
            'proprietaire' => ['boolean'],
            'date_naissance' => ['nullable','bail','date', 'before:today',
            function ($attribute,$value,$fail){
                if(Carbon::parse($value)->age < 18){
                    $fail('Le client doit être majeur(18 ans ou plus).');
                }
            }],
            'compte_bancaire' => ['boolean'],
            'piece_identite' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['nouveau', 'actif', 'inactif', 'liste_noire'])],
            // Le mot de passe (accès Espace Client) n'est jamais géré depuis le Back Office.
        ];
    }
}