<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Entity\User;
use App\Contracts\AuthInterface;
use App\Exception\ValidationException;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Valitron\Validator;
use Slim\Views\Twig;

class AuthController
{
    public function __construct(private  Twig $twig, private EntityManager $entityManager,
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
        $data = $request->getParsedBody();

        $v = new Validator($data);

        $v->rule('required', ['name', 'email', 'password', 'confirmPassword']);
        $v->rule('email', 'email');
        $v->rule('equals', 'confirmPassword', 'password')->label('Confirm Password');
        $v->rule(
            fn($field, $value, $params, $fields) => ! $this->entityManager->getRepository(User::class)->count(
                ['email' => $value]
            ),
            'email'
        )->message('User with the given email address already exists');

        if ($v->validate()) {
            echo "Yay! We're all good!";
        } else {
            throw new ValidationException($v->errors());
        }

        $user = new User();

        $user->setName($data['name']);
        $user->setEmail($data['email']);
        # Hassing password with BCRYPT: actualmente uno de los metodos mas seguros para hassing
        # el cost significa el rendimiento del hassing cuanto mas alto mas consume pero mas seguro es lo estandard es 10
        $user->setPassword(password_hash($data['password'],PASSWORD_BCRYPT,['cost' => 10]));

        # Tells the EntityManager to make an instance managed and persistent.
        # The entity will be entered into the database at or before transaction commit or as a result of the flush operation.
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    public function logIn(Request $request, Response $response): Response
    {
        // 1. Validate the request data
        $data = $request->getParsedBody();
        $v = new Validator($data);
        $v->rule('required', ['email', 'password']);
        $v->rule('email', 'email');

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