<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\SessionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StartSessionsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Si la session ya existe tenemos un problema ya que no es un comportamiento normal
        if (session_status() === PHP_SESSION_ACTIVE){
            throw new SessionException('Session has already been started');
        }

        // Revisamos que los headers se han enviado y si ese es el caso manejamos el error
        if (headers_sent($filename,$line)){
            throw new SessionException('Headers already sent');
        }

        // Iniciamos la session, para poder usarlas.
        session_start();

        // Devolvemos la respuesta con la session iniciada
        $response = $handler->handle($request);

        // con esto lo que hacemos es guardar los datos de la session y luego eliminar la session, asi podemos evitar algunos bloqueos de algunos scripts
        session_write_close();
        return $response;
    }

}