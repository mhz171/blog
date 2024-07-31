<?php

namespace Post\Controller;


use InvalidArgumentException;

use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

use Post\Controller\Plugin\AuthPlugin;
use Post\Service\PostService;
use Post\Form\PostForm;




class PostController extends AbstractActionController
{
    private PostService $postService;
    private $user;
    private $auth;
    private $isLoggedIn;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;

        $this->auth = $this->plugin(AuthPlugin::class);
        $this->user = $this->auth->getUser();
        $this->isLoggedIn = $this->auth->isLoggedIn();
    }


    public function indexAction(): ViewModel
    {
        $limit = $this->params()->fromQuery('limit', 10);
        $page = $this->params()->fromQuery('page', 1);

        $settingPaginator = $this->postService->getPaginatedPosts($page, $limit);

        return new ViewModel([
            'isLoggedIn' => $this->isLoggedIn,
            'user' => $this->user,
            'posts' => $settingPaginator['posts'],
            'currentPage' => $page,
            'totalPages' => $settingPaginator['totalPages'],
        ]);
    }

    public function addAction(): array|Response
    {
        if (!$this->isLoggedIn){
            return $this->redirect()->toRoute('login');
        }

        $request = $this->getRequest();

        $form = new PostForm();
        $form->get('submit')->setValue('Add');

        if (!$request->isPost()) {
            return ['form' => $form];
        }

        $data = ArrayUtils::iteratorToArray($request->getPost());
        $fileData = $request->getFiles();

        try {
            $this->postService->addPost($data, $fileData, $this->user);
            return $this->redirect()->toRoute('post');
        } catch (InvalidArgumentException $ex) {
            $errors = json_decode($ex->getMessage(), true);
            $form->setMessages($errors);
        }

        return [
            'isLoggedIn' => $this->isLoggedIn,
            'form' => $form,
        ];
    }

    public function editAction(): Response|array
    {
        if (!$this->isLoggedIn){
            return $this->redirect()->toRoute('login');
        }

        $request = $this->getRequest();
        $id = (int)$this->params()->fromRoute('id', 0);

        if (!$request->isPost() && 0 === $id) {
            return $this->redirect()->toRoute('post', ['action' => 'add']);
        }

        $post = $this->postService->getPostById($id);
        $form = new PostForm();
        $form->bind($post);
        $form->get('submit')->setAttribute('value', 'Edit');

        $form->setInputFilter($post->getInputFilter());

        $form->setData($request->getPost());
        $fileData = $request->getFiles();
        $data = ArrayUtils::iteratorToArray($request->getPost());

        try {
            $this->postService->updatePost($post, $data, $fileData);
            return $this->redirect()->toRoute('post', ['action' => 'index']);
        } catch (InvalidArgumentException $ex) {
            $errors = json_decode($ex->getMessage(), true);
            $form->setMessages($errors);
        }

        return [
            'postUserId' => $post->user->getId(),
            'userId' => $this->user ? $this->user->getId() : null,
            'postId' => $id,
            'form' => $form,
            'isLoggedIn' => $this->isLoggedIn,
        ];

    }

    public function deleteAction(): Response|array
    {
        if (!$this->isLoggedIn){
            return $this->redirect()->toRoute('login');
        }

        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('post');
        }

        $request = $this->getRequest();
        $post = $this->postService->getPostById($id);

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
            $this->postService->deletePost($post);
        }

        return $this->redirect()->toRoute('post');


    }

    public function createAction(): JsonModel
    {

        $request = $this->getRequest();
        $file = $request->getFiles();
        if (!$request->isPost()) {
            return new JsonModel([
                'success' => false,
                'message' => 'Your request was not accepted',
            ]);
        }

        $data = json_decode($request->getContent(), true);

        try {
            $res = $this->postService->apiAddPost($data, $file);
        }catch (\Exception $e) {
            return new JsonModel([
                'success' => false,
                'message' => 'Failed to create post and comment: ' . $e->getMessage()
            ]);
        }

        return new JsonModel($res);
    }
}
