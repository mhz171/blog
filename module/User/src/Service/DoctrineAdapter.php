<?php
// module/User/src/Service/DoctrineAdapter.php

namespace User\Service;

use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Result;
use Laminas\Session\Container;
use User\Entity\User;

class DoctrineAdapter implements AdapterInterface
{
    private $username;
    private $password;
    private $entityManager;
    private $authService;

    public function __construct(AuthenticationService $authService, EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
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

        if ($this->password != $user->getPassword()) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, ['Invalid username or password.']);
        }

        return new Result(Result::SUCCESS, $user, ['Authenticated successfully.']);
    }

    public function loginManager($data, $authAdapter)
    {
        $this->authService->setAdapter($authAdapter);
        $this->setUsername($data['username']);
        $this->setPassword($data['password']);

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => $data['username']]);

        if ($user) {

            try {
                $result = $this->authService->authenticate();

            } catch (InvalidArgumentException $e) {

                var_dump($e->getMessage());
            }
            if ($result->isValid()) {
                $user = $result->getIdentity();
                // Store user in session
                $session = new Container('user');
                $session->user = $user;

            }
//                     Store user in session or perform other actions
            return [
                'user' => $user,
                'result' => $result,
            ];

        } else{
            $user = new User();
            $user->setUsername($data['username']);
            $user->setPassword($data['password']);
            $user->setFirstname("asdfasdf");
            $user->setLastname("asdfasdf");
            date_default_timezone_set("Asia/Tehran");
            $user->setCreatedAt(new \DateTime());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->loginManager($data, $authAdapter) ;
        }
    }
}
