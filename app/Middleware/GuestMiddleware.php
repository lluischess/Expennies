<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Contracts\SessionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GuestMiddleware implements MiddlewareInterface
{
    public function __construct(private ResponseFactoryInterface $responseFactory,
                                private SessionInterface $session)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if($this->session->get('user')){
            // Redirigimos al inicio y eso revisara si tiene que logearse o no
            return $this->responseFactory->createResponse(302)->withHeader('Location','/');
        }

        // Sigue con la respuesta al front
        return $handler->handle($request);
    }

}