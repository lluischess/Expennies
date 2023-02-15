<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Contracts\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StartSessionsMiddleware implements MiddlewareInterface
{
    public function __construct(private SessionInterface $session)
    {

    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Esto es la logica de las sessiones.
        $this->session->start();

        // Devolvemos la respuesta con la session iniciada
        $response = $handler->handle($request);

        if ($request->getMethod() === 'GET'){
            $this->session->put('previousURL', (string) $request->getUri());
        }
        // con esto lo que hacemos es guardar los datos de la session y luego eliminar la session, asi podemos evitar algunos bloqueos de algunos scripts
        // esto cierra la session
        $this->session->save();

        return $response;
    }

}