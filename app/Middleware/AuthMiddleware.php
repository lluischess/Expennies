<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Contracts\AuthInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Views\Twig;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private ResponseFactoryInterface $responseFactory,
                                private AuthInterface $auth,
                                private Twig $twig)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {


        // Si el user existe obteniendo el return de auth->user() entondes gestionamos el acceso
        if($user = $this->auth->user()){
            // le pasamos al entorno global de twig el id del usuario y su nombre
            $this->twig->getEnvironment()->addGlobal('auth', ['id' => $user->getId(), 'name' => $user->getName()]);
            // Redirigimos al Login
            return $handler->handle($request->withAttribute('user', $user));
        }

        // Redirigimos a la pagina del login ya que el $user es null
        return $this->responseFactory->createResponse(302)->withHeader('Location','/login');
    }

}