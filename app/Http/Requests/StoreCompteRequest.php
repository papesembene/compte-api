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
            'client' => ['required', 'array'],
            'client.id' => ['nullable', 'uuid', 'exists:clients,id'],
            'client.titulaire' => ['required', 'string', 'max:255'],
            'client.nci' => ['required', 'string', 'unique:clients,nci', new \App\Rules\NciRule()],
            'client.email' => ['required', 'email', 'unique:clients,email'],
            'client.telephone' => ['required', 'string', 'unique:clients,telephone', new \App\Rules\PhoneRule()],
            'client.adresse' => ['required', 'string'],
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
            'client.required' => 'Les informations du client sont obligatoires.',
            'client.id.uuid' => 'L\'ID du client doit être un UUID valide.',
            'client.id.exists' => 'Le client sélectionné n\'existe pas.',
            'client.titulaire.required' => 'Le titulaire est obligatoire.',
            'client.nci.required' => 'Le NCI est obligatoire.',
            'client.nci.unique' => 'Ce NCI est déjà utilisé.',
            'client.email.required' => 'L\'email est obligatoire.',
            'client.email.email' => 'L\'email doit être valide.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.required' => 'Le téléphone est obligatoire.',
            'client.telephone.unique' => 'Ce téléphone est déjà utilisé.',
            'client.adresse.required' => 'L\'adresse est obligatoire.',
        ];
    }
}