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

        return $data;
    }

}