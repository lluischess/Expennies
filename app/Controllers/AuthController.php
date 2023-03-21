<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Contracts\AuthInterface;
use App\Exception\ValidationException;
use App\RequestValidators\RegisterUserRequestValidator;
use App\Contracts\RequestValidatorFactoryInterface;
use App\RequestValidators\UserLoginRequestValidator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Valitron\Validator;
use Slim\Views\Twig;

class AuthController
{
    public function __construct(private  Twig $twig, private RequestValidatorFactoryInterface $requestValidatorFactory,
                                private AuthInterface $auth)
    {
    }

    public function loginView(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'auth/login.twig');
    }

    public function registerView(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response): Response
    {
        // Crea y valida el objeto de validación de datos de la solicitud de registro de usuario
        $data = $this->requestValidatorFactory->make(RegisterUserRequestValidator::class)->validate($request->getParsedBody());

        // Registra al usuario utilizando los datos validados
        $this->auth->register($data);

        // Redirige al usuario a la página de inicio de sesión y devuelve un código de estado HTTP 302 (Redirección temporal)
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    public function logIn(Request $request, Response $response): Response
    {
        // 1. Validate the request data
        $data = $this->requestValidatorFactory->make(UserLoginRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        // Gestiona el login
        if(! $this->auth->attemptLogin($data)) {
            throw new ValidationException(['password' => ['You have entered an invalid username or password']]);
        }

        // 4. Redirect the user to the home page
        return $response->withHeader('Location', '/')->withStatus(302);
    }

    public function logOut(Request $request, Response $response): Response
    {
        $this->auth->logOut();

        return $response->withHeader('Location', '/')->withStatus(302);
    }

}