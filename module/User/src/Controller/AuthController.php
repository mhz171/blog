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

    private $authAdapter;

    public function __construct(DoctrineAdapter $authAdapter)
    {

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

                $loginInfo = $this->authAdapter->loginManager($data, $this->authAdapter);


                if ($loginInfo['result']->isValid()) {
                    return $this->redirect()->toRoute('post');
                } else {
                    $form->setMessages(['password' => $loginInfo['result']->getMessages()]);
                    return new ViewModel([
                        'form' => $form,
                    ]);
                }
            }
        }
        return new ViewModel([
            'form' => $form,
        ]);
    }
}
