<?php

namespace App\Http\Requests;

use App\Rules\NumeroCompteRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'numero_compte' => ['required', 'string', 'unique:comptes,numero_compte', new NumeroCompteRule()],
            'solde_initial' => ['nullable', 'numeric', 'min:0'],
            'type_compte' => ['required', 'string', 'in:courant,epargne'],
            'client_id' => ['required', 'uuid', 'exists:clients,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_compte.required' => 'Le champ numéro de compte est obligatoire.',
            'numero_compte.unique' => 'Ce numéro de compte est déjà utilisé.',
            'solde_initial.numeric' => 'Le solde initial doit être un nombre.',
            'solde_initial.min' => 'Le solde initial ne peut pas être négatif.',
            'type_compte.required' => 'Le champ type de compte est obligatoire.',
            'type_compte.in' => 'Le type de compte doit être courant ou epargne.',
            'client_id.required' => 'Le champ client est obligatoire.',
            'client_id.uuid' => 'L\'ID du client doit être un UUID valide.',
            'client_id.exists' => 'Le client sélectionné n\'existe pas.',
        ];
    }
}