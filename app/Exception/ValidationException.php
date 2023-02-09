<?php

declare(strict_types=1);

namespace App\Exception;

use Throwable;

class ValidationException extends \RuntimeException
{

    /**
     * @param array|bool $errors
     */
    public function __construct(
        public array $errors, // Lista de errores
        string $message = 'Validation Errors', // tipo de mensaje de error, en este caso son errores de validación
        int $code = 422, // Codigo de error 422(Entidad no procesable)
        ?Throwable $previous = null) // The previous throwable used for the exception chaining
    {
        parent::__construct($message, $code, $previous);
    }
}