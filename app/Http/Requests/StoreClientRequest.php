<?php

namespace App\Http\Requests;

use App\Rules\NciRule;
use App\Rules\PhoneRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
            'titulaire' => ['required', 'string', 'max:255'],
            'nci' => ['required', 'string', 'unique:clients,nci', new NciRule()],
            'email' => ['required', 'email', 'unique:clients,email'],
            'telephone' => ['required', 'string', 'unique:clients,telephone', new PhoneRule()],
            'adresse' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulaire.required' => 'Le champ titulaire est obligatoire.',
            'titulaire.string' => 'Le titulaire doit être une chaîne de caractères.',
            'titulaire.max' => 'Le titulaire ne peut pas dépasser 255 caractères.',
            'nci.required' => 'Le champ NCI est obligatoire.',
            'nci.unique' => 'Ce NCI est déjà utilisé.',
            'email.required' => 'Le champ email est obligatoire.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'telephone.required' => 'Le champ téléphone est obligatoire.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'adresse.required' => 'Le champ adresse est obligatoire.',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
        ];
    }
}