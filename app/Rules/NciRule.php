<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Règle de validation pour les numéros NCI (Carte d'Identité Nationale).
 *
 * Responsabilité : Valider le format du NCI selon les spécifications.
 */
class NciRule implements ValidationRule
{
    /**
     * Exécute la règle de validation.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Le NCI est un numéro de 13 chiffres
        if (!preg_match('/^[0-9]{13}$/', $value)) {
            $fail('Le :attribute doit être un numéro NCI valide de 13 chiffres.');
        }
    }
}