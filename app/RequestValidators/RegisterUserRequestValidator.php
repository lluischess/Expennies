<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use Doctrine\ORM\EntityManager;
use Valitron\Validator;
use App\Entity\User;
use App\Exception\ValidationException;

class RegisterUserRequestValidator implements RequestValidatorInterface
{
    public function __construct(private EntityManager $entityManager)
    {
    }

    public function validate(array $data): array
    {
        $v = new Validator($data);

        // Validamos los datos del registro del usuario
        $v->rule('required', ['name', 'email', 'password', 'confirmPassword']);
        $v->rule('email', 'email');
        $v->rule('equals', 'confirmPassword', 'password')->label('Confirm Password');
        $v->rule(
            // si 0 es es false significa que no hay ningun email en la BBDD y puede registrarse en casa de ser true 1 pasaremos un error con un msg
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

        return $data;
    }

}