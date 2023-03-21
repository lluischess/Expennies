<?php

declare(strict_types = 1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorFactoryInterface;
use App\Contracts\RequestValidatorInterface;
use Psr\Container\ContainerInterface;

class RequestValidatorFactory implements RequestValidatorFactoryInterface
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

public function make(string $class): RequestValidatorInterface
{
    // Obtiene una instancia de la clase validadora de solicitudes del contenedor de dependencias
    $validator = $this->container->get($class);

    // Verifica si la instancia creada implementa la interfaz RequestValidatorInterface
    if ($validator instanceof RequestValidatorInterface) {
        // Si es así, devuelve la instancia del validador de solicitudes
        return $validator;
    }

    //Si la instancia no implementa la interfaz RequestValidatorInterface, lanza una excepción
    throw new \RuntimeException('Failed to instantiate the request validator class "' . $class . '"');
}
}