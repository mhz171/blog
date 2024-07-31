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
use Post\Service\PostServiceInterface;


class PostController extends AbstractActionController
{
    private PostServiceInterface $postService;
    private $user;
    private $auth;
    private $isLoggedIn;

    public function __construct(PostServiceInterface $postService)
    {
        $this->postService = $postService;

        // check for login user
        $this->auth = $this->plugin(AuthPlugin::class);
        $this->user = $this->auth->getUser();
        $this->isLoggedIn = $this->auth->isLoggedIn();
    }


    public function indexAction(): ViewModel
    {
        //get page number and limit in url
        $limit = $this->params()->fromQuery('limit', 10);
        $page = $this->params()->fromQuery('page', 1);

        //settingPaginator contain post, currentPage and totalPage
        $settingPaginator = $this->postService->getPaginatedPosts($page, $limit);

        return new ViewModel([
            'isLoggedIn' => $this->isLoggedIn,
            'user' => $this->user,
            'posts' => $settingPaginator['posts'],
            'currentPage' => $settingPaginator['currentPage'],
            'totalPages' => $settingPaginator['totalPages'],
        ]);
    }

    public function addAction(): array|null
    {
        //check user is login
        if (!$this->isLoggedIn) {
            $this->redirect()->toRoute('login');
            return null;
        }

        $request = $this->getRequest();

        //create form for view
        $form = new PostForm();
        $form->get('submit')->setValue('Add');

        if (!$request->isPost()) {
            return [
                'isLoggedIn' => $this->isLoggedIn,
                'form' => $form,
            ];
        }

        //converse data in request to array
        $data = ArrayUtils::iteratorToArray($request->getPost());
        $fileData = $request->getFiles();

        //result have success, userLoginStatus, titleStatus, descriptionStatus, addCommentStatus
        $result = $this->postService->addPost($data, $fileData, $this->user);

        //check result if post successfully added redirect to main page else set error
        if ($result['success']) {
            $this->redirect()->toRoute('post');
            return null;
        } else {
            $errors = [];
            if (!$result['titleStatus']) {
                $errors['title'][] = [
                    'Title is require'
                ];
            }
            if (!$result['descriptionStatus']) {
                $errors['description'][] = [
                    'Description is require'
                ];
            } else if (!$result['addCommentStatus']) {
                $errors['description'][] = [
                    'Can not add post. Please try again later!'
                ];
            }
            $form->setMessages($errors);
        }

        //return to view
        return [
            'isLoggedIn' => $this->isLoggedIn,
            'form' => $form,
        ];
    }

    public function editAction(): null|array
    {
        //check user is login
        if (!$this->isLoggedIn) {
            $this->redirect()->toRoute('login');
            return null;

        }

        $request = $this->getRequest();

        //get postId from url
        $id = (int)$this->params()->fromRoute('id', 0);

        //if postId is zero its mean we don't have this post then go to add page
        if (!$request->isPost() && 0 === $id) {
            $this->redirect()->toRoute('post', ['action' => 'add']);
            return null;
        }

        //get post for edit
        $post = $this->postService->getPostById($id);

        //create post form for view
        $form = new PostForm();
        $form->bind($post);
        $form->get('submit')->setAttribute('value', 'Edit');
        //set filter for validation
        $form->setInputFilter($post->getInputFilter());

        $form->setData($request->getPost());

        //get file data for image
        $fileData = $request->getFiles();

        //convert data from request
        $data = ArrayUtils::iteratorToArray($request->getPost());

        // show data to user
        if (!$request->isPost()) {
            return [
                'postUserId' => $post->user->getId(),
                'userId' => $this->user ? $this->user->getId() : null,
                'postId' => $id,
                'form' => $form,
                'isLoggedIn' => $this->isLoggedIn,
            ];
        }

        // update post with new data
        $result = $this->postService->updatePost($post, $data, $fileData);

        //check result if post successfully added redirect to main page else set error
        if ($result['success']) {
            $this->redirect()->toRoute('post', ['action' => 'index']);
        } else {
            $errors = [];
            if (!$result['titleStatus']) {
                $errors['title'][] = [
                    'Title is require'
                ];
            }
            if (!$result['descriptionStatus']) {
                $errors['description'][] = [
                    'Description is require'
                ];
            }
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

    public function deleteAction(): null|array
    {
        //check user is login
        if (!$this->isLoggedIn) {
            $this->redirect()->toRoute('login');
            return null;
        }
        //get post id from url for delete
        $id = (int)$this->params()->fromRoute('id', 0);

        // if id is zero this post does not exist
        if (!$id) {
            $this->redirect()->toRoute('post');
            return null;
        }

        $request = $this->getRequest();

        //get post from db for show user
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
        //get request for delete post from view
        $del = $request->getPost('del', 'No');

        // delete post
        if ($del == 'Yes') {
            $this->postService->deletePost($post);
        }

        //back to menu
        $this->redirect()->toRoute('post');
        return null;
    }

    public function addApiAction(): JsonModel
    {
        $request = $this->getRequest();

        // if send method is not post
        if (!$request->isPost()) {
            return new JsonModel([
                'success' => false,
                'message' => 'Your request was not accepted',
            ]);
        }

        //extract data from input json
        $data = json_decode($request->getContent(), true);

        //find user who want to add post
        $user = $this->postService->getUserById($data['user_id']);
        if (!$user) {
            return new JsonModel([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        $res = $this->postService->AddPost($data, $data['image'], $user);

        //check result for successfully added or set error
        if ($res['success']) {
            return new JsonModel([
                'success' => true,
                'message' => 'Post added successfully.'
            ]);
        } else if (!$res['titleStatus'] || !$res['descriptionStatus']) {
            return new JsonModel([
                'success' => false,
                'message' => 'title or description is not valid.'
            ]);
        } else {
            return new JsonModel([
                'success' => false,
                'message' => 'comment can\'t be added.'
            ]);
        }
    }
}
