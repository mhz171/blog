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

    public function addAction(): array|null
    {
        if (!$this->isLoggedIn){
            $this->redirect()->toRoute('login');
            return null;
        }

        $request = $this->getRequest();

        $form = new PostForm();
        $form->get('submit')->setValue('Add');

        if (!$request->isPost()) {
            return [
                'isLoggedIn' => $this->isLoggedIn,
                'form' => $form,
                ];
        }

        $data = ArrayUtils::iteratorToArray($request->getPost());
        $fileData = $request->getFiles();


        $result  = $this->postService->addPost($data, $fileData, $this->user);

        if ($result['success']) {
            $this->redirect()->toRoute('post');
            return null;
        }else {
            $errors = [];
            if (!$result['titleStatus']){
                $errors['title'][] = [
                    'Title is require'
                ];
            }
            if (!$result['descriptionStatus']){
                $errors['description'][] = [
                    'Description is require'
                ];
            }
            else if (!$result['addCommentStatus']){
                $errors['description'][] = [
                    'Can not add post. Please try again later!'
                ];
            }
            $form->setMessages($errors);
        }

        return [
            'isLoggedIn' => $this->isLoggedIn,
            'form' => $form,
        ];
    }

    public function editAction(): null|array
    {
        if (!$this->isLoggedIn){
            $this->redirect()->toRoute('login');
            return null ;

        }

        $request = $this->getRequest();
        $id = (int)$this->params()->fromRoute('id', 0);

        if (!$request->isPost() && 0 === $id) {
            $this->redirect()->toRoute('post', ['action' => 'add']);
            return null;
        }

        $post = $this->postService->getPostById($id);
        $form = new PostForm();
        $form->bind($post);
        $form->get('submit')->setAttribute('value', 'Edit');

        $form->setInputFilter($post->getInputFilter());

        $form->setData($request->getPost());
        $fileData = $request->getFiles();
        $data = ArrayUtils::iteratorToArray($request->getPost());


        if (!$request->isPost())
        {
            return [
                'postUserId' => $post->user->getId(),
                'userId' => $this->user ? $this->user->getId() : null,
                'postId' => $id,
                'form' => $form,
                'isLoggedIn' => $this->isLoggedIn,
            ];
        }


        $result = $this->postService->updatePost($post, $data, $fileData);



        if ($result['success']) {
            $this->redirect()->toRoute('post', ['action' => 'index']);
            return [
                'postUserId' => $post->user->getId(),
                'userId' => $this->user ? $this->user->getId() : null,
                'postId' => $id,
                'form' => $form,
                'isLoggedIn' => $this->isLoggedIn,
            ];
        }else {
//            var_dump(1234);exit();
            $errors = [];
            if (!$result['titleStatus']){
                $errors['title'][] = [
                    'Title is require'
                ];
            }
            if (!$result['descriptionStatus']){
                $errors['description'][] = [
                    'Description is require'
                ];
            }
            $form->setMessages($errors);
            return [
                'postUserId' => $post->user->getId(),
                'userId' => $this->user ? $this->user->getId() : null,
                'postId' => $id,
                'form' => $form,
                'isLoggedIn' => $this->isLoggedIn,
            ];

        }



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

    public function addApiAction(): JsonModel
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

        $user = $this->postService->getUserById($data['user_id']);
        if (!$user) {
            return new JsonModel([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        $res = $this->postService->AddPost($data, $data['image'], $user);

         if ($res['success'])
         {
             return new JsonModel([
                 'success' => true,
                 'message' => 'Post added successfully.'
             ]);
         }else if (!$res['titleStatus'] || !$res['descriptionStatus'])
         {
             return new JsonModel([
                'success' => false,
                'message' => 'title or description is not valid.'
             ]);
         }else
         {
             return new JsonModel([
                 'success' => false,
                 'message' => 'comment can\'t be added.'
             ]);
         }


    }
}
