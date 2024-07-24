<?php

namespace Post\Controller;


use InvalidArgumentException;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Session\Container;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Model\ViewModel;
use Laminas\Paginator\Paginator;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\ORM\EntityManager;

use Post\Service\PostService;
use Post\Form\PostForm;
use Post\Entity\Post;
use User\Entity\User;



class PostController extends AbstractActionController
{
    private $serviceManager;
    private $user;

    public function __construct(PostService $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        $session = new Container('user');
        $user = $session->user;
        $this->user = $user;
    }

    public function indexAction()
    {


        try {
            $page = $this->params()->fromQuery('page', 1);
            $limit = 5;

            $paginator = $this->serviceManager->getPaginatedPosts($page, $limit);

            return new ViewModel([
                'paginator' => $paginator,
                'user' => $this->user
            ]);
        }catch (\Exception $ex){
            var_dump($ex->getMessage());
        }

    }

    public function addAction()
    {

        $request = $this->getRequest();
        $form = new PostForm();
        $form->get('submit')->setValue('Add');

        if (!$request->isPost()) {
            return ['form' => $form];
        }

        $data = ArrayUtils::iteratorToArray($request->getPost());
        $fileData = $request->getFiles();

        try {
            $this->serviceManager->addPost($data, $fileData, $this->user);
            return $this->redirect()->toRoute('post');
        } catch (InvalidArgumentException $ex) {
            // Add error message to form
            $errors = json_decode($ex->getMessage(), true);
            var_dump($errors);
            $form->setMessages($errors);
        }

        return [
            'form' => $form,
        ];

    }

    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('post', ['action' => 'add']);
        }

        $post = $this->serviceManager->getPostById($id);

        $form = new PostForm();
        $form->bind($post);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form];

        if (!$request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($post->getInputFilter());

        $form->setData($request->getPost());
        $fileData = $request->getFiles();
        $data = ArrayUtils::iteratorToArray($request->getPost());
        try {
            $this->serviceManager->updatePost($post, $data, $fileData);
            return $this->redirect()->toRoute('post', ['action' => 'index']);
        } catch (InvalidArgumentException $ex) {
            $errors = json_decode($ex->getMessage(), true);
            $form->setMessages($errors);
        }

        return $viewData;

    }

    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('post');
        }

        $request = $this->getRequest();
        $post = $this->serviceManager->getPostById($id);

        if (!$request->isPost()) {
            return [
                'id' => $id,
                'post' => $post,
            ];
        }

        $del = $request->getPost('del', 'No');

        if ($del == 'Yes') {
            $this->serviceManager->deletePost($post);
        }

        return $this->redirect()->toRoute('post');


    }
}
