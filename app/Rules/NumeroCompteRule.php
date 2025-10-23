<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Règle de validation pour les numéros de compte bancaire.
 *
 * Responsabilité : Valider le format du numéro de compte.
 */
class NumeroCompteRule implements ValidationRule
{
    /**
     * Exécute la règle de validation.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Format du numéro de compte : 10 chiffres commençant par 1-9
        if (!preg_match('/^[1-9][0-9]{9}$/', $value)) {
            $fail('Le :attribute doit être un numéro de compte valide de 10 chiffres commençant par un chiffre non nul.');
        }
    }
}