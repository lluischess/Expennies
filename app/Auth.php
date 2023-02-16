<?php

declare(strict_types=1);

namespace App;

use App\Contracts\AuthInterface;
use App\Contracts\UserInterface;
use App\Contracts\UserProviderServiceInterface;
use App\Contracts\SessionInterface;
use Doctrine\ORM\EntityManager;

class Auth implements AuthInterface
{
    private ?UserInterface $user = null;

    public function __construct(private  UserProviderServiceInterface $userProvider, private SessionInterface $session)
    {
    }

    public function user(): ?UserInterface
    {
        if ($this->user !== null) {
            return $this->user;
        }

        //$userId = $_SESSION['user'] ?? null;
        $userId = $this->session->get('user');

        if (! $userId) {
            return null;
        }

        $user = $this->userProvider->getById($userId);

        if (! $user) {
            return null;
        }

        $this->user = $user;

        return $this->user;

    }

    public function attemptLogin(array $credentials): bool
    {
        # Hacemos la query para mirar si existe el usuario
        $user = $this->userProvider->getByCredentials($credentials);

        # Si no existe el usuario o no tiene correctas las credenciales devolvemos false
        if (! $user || ! $this->checkCredentials($user, $credentials)) {
            return false;
        }

        $this->logIn($user);

        return true;
    }

    public function checkCredentials(UserInterface $user, array $credentials): bool
    {
        return password_verify($credentials['password'], $user->getPassword());
    }

    public function logOut(): void
    {
        //unset($_SESSION['user']);
        $this->session->forget('user');
        $this->session->regenerate();

        $this->user = null;
    }

    public function register(array $data): UserInterface
    {
        $user = $this->userProvider->createUser($data);

        // Authenticate the user
        $this->logIn($user);

        return $user;
    }

    public function logIn(UserInterface $user): void
    {
        # Regeneramos la id de la session de las cookies para evitar ataques de hackers
        //session_regenerate_id();
        $this->session->regenerate();

        # Guardamos el id del usuario en la session
        //$_SESSION['user'] = $user->getId();
        $this->session->put('user', $user->getId());

        # Le pasamos los datos del usuario a las propiedades de la clase
        $this->user = $user;
    }
}