<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception levée lorsqu'un compte n'est pas trouvé.
 */
class CompteNotFoundException extends Exception
{
    public function __construct(string $message = 'Compte non trouvé', int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}