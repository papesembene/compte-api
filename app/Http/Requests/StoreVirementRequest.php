<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVirementRequest extends FormRequest
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
            'compte_source' => ['required', 'string', 'uuid', 'exists:comptes,id'],
            'compte_destination' => ['required', 'string', 'uuid', 'exists:comptes,id', 'different:compte_source'],
            'montant' => ['required', 'numeric', 'min:100', 'max:10000000'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'compte_source.required' => 'Le compte source est obligatoire.',
            'compte_source.uuid' => 'Le compte source doit être un UUID valide.',
            'compte_source.exists' => 'Le compte source n\'existe pas.',
            'compte_destination.required' => 'Le compte destination est obligatoire.',
            'compte_destination.uuid' => 'Le compte destination doit être un UUID valide.',
            'compte_destination.exists' => 'Le compte destination n\'existe pas.',
            'compte_destination.different' => 'Le compte destination doit être différent du compte source.',
            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant minimum est de 100 FCFA.',
            'montant.max' => 'Le montant maximum est de 10 000 000 FCFA.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 255 caractères.',
        ];
    }
}
