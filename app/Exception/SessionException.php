<?php

declare(strict_types=1);

namespace App\Middleware;

use RuntimeException;

class SessionException extends RuntimeException
{

    /**
     * @param string $string
     */
    public function __construct(string $string)
    {

    }
}