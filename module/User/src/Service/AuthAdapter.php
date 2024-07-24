<?php

namespace User\Service;

use Doctrine\ORM\EntityManager;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;
use User\Entity\User;

class AuthAdapter implements AdapterInterface
{
    private $username;
    private $password;
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function authenticate()
    {
        $repository = $this->entityManager->getRepository(User::class);
        $user = $repository->findOneBy(['username' => $this->username]);

        if (!$user) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null, ['Invalid username or password.']);
        }

        if ($user->getPassword() !== $this->password) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, ['Invalid username or password.']);
        }

        return new Result(Result::SUCCESS, $user, ['Authenticated successfully.']);
    }
}
