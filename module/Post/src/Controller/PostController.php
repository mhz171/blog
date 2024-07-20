<?php

namespace Post\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Post\Model\PostTable;
use Post\Form\PostForm;
use Post\Model\Post;
use Post\Service\PostService;

class PostController extends AbstractActionController
{
    private $table;
    public function __construct(PostTable $table){
        $this->table = $table;
    }
    public function indexAction()
    {
        return new ViewModel(['posts' => $this->table->fetchAll()]);
    }
    public function addAction()
    {
        $form = new PostForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();

        if (! $request->isPost()) {
            return ['form' => $form];
        }
//        $data = $this->params()->fromPost();
//
//        $validationResult = $this->postService->validatePostData();
//        if ($validationResult['status'] == 'error') {
//            return new ViewModel([
//                'form' => $form,
//                'errors' => $validationResult['messages'],
//            ]);
//        }
        $post = new Post();
        

        $form->setInputFilter($post->getInputFilter());
        $form->setData($request->getPost());

        $postService = new PostService($form);
        // $validationResult = $postService->isValid();

        if (! $form->isValid()) {
            return ['form' => $form];
        }

        $post->exchangeArray($form->getData());
        $this->table->savepost($post);
        return $this->redirect()->toRoute('post');
    }
    public function editAction()
    {

        $id = (int) $this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('post', ['action' => 'add']);
        }

        // Retrieve the album with the specified id. Doing so raises
        // an exception if the album is not found, which should result
        // in redirecting to the landing page.
        try {
            $post = $this->table->getPost($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('post', ['action' => 'index']);
        }

        $form = new PostForm();
        $form->bind($post);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form];

        if (! $request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($post->getInputFilter());
        $form->setData($request->getPost());

        if (! $form->isValid()) {
            return $viewData;
        }

        $this->table->savePost($post);

        // Redirect to album list
        return $this->redirect()->toRoute('post', ['action' => 'index']);

    }
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('post');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->table->deletePost($id);
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('post');
        }

        return [
            'id'    => $id,
            'post' => $this->table->getPost($id),
        ];
    }
}
