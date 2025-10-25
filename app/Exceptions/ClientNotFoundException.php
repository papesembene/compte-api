<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception levée lorsqu'un client n'est pas trouvé.
 */
class ClientNotFoundException extends Exception
{
    public function __construct(string $message = 'Client non trouvé', int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}