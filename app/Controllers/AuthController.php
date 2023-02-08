<?php

declare(strict_types = 1);

namespace App\Controllers;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class AuthController
{
    public function __construct(private  Twig $twig, private EntityManager $entityManager)
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
    // TODO: Implement User Registration
    $data = $request->getParsedBody($this);

    $user = new User();

    $user->setName($data['name']);
    $user->setEmail($data['email']);
    # Hassing password with BCRYPT: actualmente uno de los metodos mas seguros para hassing
    # el cost significa el rendimiento del hassing cuanto mas alto mas consume pero mas seguro es lo estandard es 10
    $user->setPassword(password_hash($data['password'],PASSWORD_BCRYPT,['cost' => 10]));

//    Tells the EntityManager to make an instance managed and persistent.
//    The entity will be entered into the database at or before transaction commit or as a result of the flush operation.
    $this->entityManager->persist($user);
    $this->entityManager->flush();

    return $response;
}
}