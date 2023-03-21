<?php
declare(strict_types = 1);

namespace App\Services;

use App\Contracts\UserInterface;
use App\Contracts\UserProviderServiceInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class UserProviderService implements UserProviderServiceInterface
{
    public function __construct(private readonly EntityManager $entityManager)
    {
    }

    /*
     * Busca y devuelve un objeto de usuario en la base de datos utilizando su ID (clave primaria)
     * Si no se encuentra un usuario con ese ID, devuelve null
     */
    public function getById(int $userId): ?UserInterface
    {
        return $this->entityManager->find(User::class, $userId);
    }


    /*
     * Busca y devuelve un objeto de usuario en la base de datos utilizando su dirección de correo electrónico
     * Si no se encuentra un usuario con ese correo electrónico, devuelve null
     */
    public function getByCredentials(array $credentials): ?UserInterface
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
    }

    public function createUser(array $data): UserInterface
    {
        $user = new User();

        // Seteamos los valores de los datos que tenemos en la Array
        $user->setName($data['name']);
        $user->setEmail($data['email']);

        # Hassing password with BCRYPT: actualmente uno de los metodos mas seguros para hassing
        # el cost significa el rendimiento del hassing cuanto mas alto mas consume pero mas seguro es lo estandard es 10
        $user->setPassword(password_hash($data['password'],PASSWORD_BCRYPT,['cost' => 12]));

        # Tells the EntityManager to make an instance managed and persistent.
        # The entity will be entered into the database at or before transaction commit or as a result of the flush operation.
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Devuelve el objeto de usuario creado
        return $user;
    }

}