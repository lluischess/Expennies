<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Contracts\AuthInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private ResponseFactoryInterface $responseFactory,
                                private AuthInterface $auth)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if($user = $this->auth->user()){
            // Redirigimos al Login
            return $handler->handle($request->withAttribute('user', $user));
        }

        // Sigue con la respuesta al front
        return $this->responseFactory->createResponse(302)->withHeader('Location','/login');
    }

}