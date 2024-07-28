<?php

namespace Post\Controller;


use InvalidArgumentException;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Session\Container;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\Paginator\Paginator;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\ORM\EntityManager;

use Post\Controller\Plugin\AuthPlugin;
use Post\Service\PostService;
use Post\Form\PostForm;
use Post\Entity\Post;
use User\Entity\User;



class PostController extends AbstractActionController
{
    private $serviceManager;
    private $user;
    private $auth;
    private $isLoggedIn;

    public function __construct(PostService $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        $this->auth = $this->plugin(AuthPlugin::class);
        $this->user = $this->auth->getUser();
        $this->isLoggedIn = $this->auth->isLoggedIn();
    }


    public function indexAction()
    {

        try {
            $page = $this->params()->fromQuery('page', 1);
            $limit = 5;

            $settingPaginator = $this->serviceManager->getPaginatedPosts($page, $limit);


            return new ViewModel([
                'user' => $this->user,
                'isLoggedIn' => $this->isLoggedIn,
                'posts' => $settingPaginator['posts'],
                'currentPage' => $page,
                'totalPages' => $settingPaginator['totalPages'],

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
            $errors = json_decode($ex->getMessage(), true);
            var_dump($errors);
            $form->setMessages($errors);
        }

        return [
            'isLoggedIn' => $this->isLoggedIn,
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
        $viewData = [
            'postUserId' => $post->user->getId(),
            'userId' => $this->user ? $this->user->getId() : null,
            'postId' => $id,
            'form' => $form,
            'isLoggedIn' => $this->isLoggedIn,
            ];

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
                'postUserId' => $post->user->getId(),
                'userId' => $this->user ? $this->user->getId() : null,
                'postId' => $id,
                'isLoggedIn' => $this->isLoggedIn,
                'post' => $post,
            ];
        }

        $del = $request->getPost('del', 'No');

        if ($del == 'Yes') {
            $this->serviceManager->deletePost($post);
        }

        return $this->redirect()->toRoute('post');


    }

    public function createAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = json_decode($request->getContent(), true);
            // اعتبارسنجی داده‌های ورودی
            if (!$this->serviceManager->validatePostData($data)) {
                return new JsonModel([
                    'success' => false,
                    'message' => 'Title, description, and user_id are required.'
                ]);
            }

            $user = $this->serviceManager->getUser($data['user_id']);
            if (!$user) {
                return new JsonModel([
                    'success' => false,
                    'message' => 'User not found.'
                ]);
            }
//
            $postId = $this->serviceManager->apiAddPost($data, $user);
//
            return new JsonModel([
                'success' => true,
                'post_id' => $postId,
            ]);
        }
    }
}
