<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exception\ValidationException;
use App\Contracts\SessionInterface;
use App\Services\RequestService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(private ResponseFactoryInterface $responseFactory,
                                private SessionInterface $session,
                                private RequestService $requestService)
    {

    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);

        } catch(ValidationException $e) {
            $response = $this->responseFactory->createResponse();
            //$referer  = $request->getServerParams()['HTTP_REFERER'];
            $referer  = $this->requestService->getReferer($request);

            // Guardamos los datos antiguos
            $oldData = $request->getParsedBody();
            // Creamos array con Datos sensibles
            $credentialFiles = ['password', 'confirmPassword'];

            //$_SESSION['errors'] = $e->errors;
            $this->session->flash('errors', $e->errors);

            // Quitamos de los datos antiguos los datos sensibles
            //$_SESSION['old'] = array_diff_key($oldData, array_flip($credentialFiles));
            $this->session->flash('old', array_diff_key($oldData, array_flip($credentialFiles)));

            return $response->withHeader('Location', $referer)->withStatus(302);
        }
    }

}