<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Règle de validation pour les numéros de téléphone sénégalais.
 *
 * Responsabilité : Valider le format des numéros de téléphone selon les spécifications locales.
 */
class PhoneRule implements ValidationRule
{
    /**
     * Exécute la règle de validation.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Format des numéros de téléphone sénégalais : commence par +221 ou 221, suivi de 9 chiffres commençant par 70, 75, 76, 77 ou 78
        if (!preg_match('/^(?:\+221|221)(70|75|76|77|78)[0-9]{7}$/', $value)) {
            $fail('Le :attribute doit être un numéro de téléphone sénégalais valide.');
        }
    }
}