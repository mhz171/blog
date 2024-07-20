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
    public function __construct(PostTable $table)
    {
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

        if (!$request->isPost()) {
            return ['form' => $form];
        }

        $post = new Post();
        $form->setInputFilter($post->getInputFilter());
        $form->setData($request->getPost());
        $fileData = $request->getFiles();
        $postService = new PostService($form);
        $validationResult = $postService->isValid();
        $image ="";
        if (!$validationResult) {
            return ['form' => $form];
        }
        if ($form->isValid() && $fileData['image']['error'] == UPLOAD_ERR_OK) {
            // Handle file upload
            $data = $form->getData();
            $file = $fileData['image'];

            // Define the target directory and file name
            $targetDir = './public/img/';
            $image = $targetDir . basename($file['name']) ;

            // Ensure the directory exists
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
//
//            // Move the uploaded file to the target directory
            if (!move_uploaded_file($file['tmp_name'], $image)) {
                $form->get('image')->setMessages(['File upload failed.']);
            }
//
        }
        $post->image = $image;
        $post->exchangeArray($form->getData());
        $post->image = $image;
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

        if (!$request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($post->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }
        $fileData = $request->getFiles();
        $image ="";

        if ($form->isValid() && $fileData['image']['error'] == UPLOAD_ERR_OK) {
            // Handle file upload
            $data = $form->getData();
            $file = $fileData['image'];

            // Define the target directory and file name
            $targetDir = './public/img/';
            $image = $targetDir . basename($file['name']) ;

            // Ensure the directory exists
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
//
//            // Move the uploaded file to the target directory
            if (!move_uploaded_file($file['tmp_name'], $image)) {
                $form->get('image')->setMessages(['File upload failed.']);
            }
//
        }
        if ($image != ""){
            $post->image = $image;
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
