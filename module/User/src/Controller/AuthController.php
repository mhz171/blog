<?php


// module/User/src/Controller/AuthController.php

namespace User\Controller;

use InvalidArgumentException;
use Laminas\Authentication\Adapter\Exception\ExceptionInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use RuntimeException;
use User\Form\LoginForm;
use Laminas\Authentication\AuthenticationService;
use User\Service\DoctrineAdapter;
use Laminas\Session\Container;

class AuthController extends AbstractActionController
{
    private $authService;
    private $authAdapter;

    public function __construct(AuthenticationService $authService, DoctrineAdapter $authAdapter)
    {
        $this->authService = $authService;
        $this->authAdapter = $authAdapter;
    }

    public function loginAction()
    {
        $form = new LoginForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $this->authAdapter->setUsername($data['username']);
                $this->authAdapter->setPassword($data['password']);

                try {
                    $result = $this->authService->authenticate();

                }catch (InvalidArgumentException $e) {

                    var_dump($e->getMessage());
                }
                if ($result->isValid()) {
                    $user = $result->getIdentity();

                    // Store user in session
                    $session = new Container('user');
                    $session->user = $user;

//                     Store user in session or perform other actions
                    return $this->redirect()->toRoute('post');
                } else {
                    $form->setMessages(['password' => $result->getMessages()]);
                }
            }
        }
        return new ViewModel([
            'form' => $form,
        ]);
    }
}
