<?php

namespace Post\Controller;

use Doctrine\ORM\EntityManager;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Post\Entity\Post;
use Post\Form\PostForm;
use Post\Service\PostService;

class PostController extends AbstractActionController
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function indexAction()
    {
        $posts = $this->entityManager->getRepository(Post::class)->findAll();
        return new ViewModel(['posts' => $posts]);
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
        $image = "";

        if (!$validationResult) {
            return ['form' => $form];
        }

        if ($form->isValid() && $fileData['image']['error'] == UPLOAD_ERR_OK) {
            // Handle file upload
            $data = $form->getData();
            $file = $fileData['image'];

            // Define the target directory and file name
            $targetDir = './public/img/';
            $image = $targetDir . basename($file['name']);

            // Ensure the directory exists
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            // Move the uploaded file to the target directory
            if (!move_uploaded_file($file['tmp_name'], $image)) {
                $form->get('image')->setMessages(['File upload failed.']);
            }
        }

        $post->setImage($image);
        $post->setTitle($form->get('title')->getValue());
        $post->setDescription($form->get('description')->getValue());
        $post->setUser($form->get('user')->getValue());
        $post->setImage($image);
        date_default_timezone_set("Asia/Tehran");
        $post->setCreatedAt(\DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $this->redirect()->toRoute('post');
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('post', ['action' => 'add']);
        }

        // Retrieve the post with the specified id
        try {
            $post = $this->entityManager->getRepository(Post::class)->find($id);
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
        $image = "";

        if ($form->isValid() && $fileData['image']['error'] == UPLOAD_ERR_OK) {
            // Handle file upload
            $data = $form->getData();
            $file = $fileData['image'];

            // Define the target directory and file name
            $targetDir = './public/img/';
            $image = $targetDir . basename($file['name']);

            // Ensure the directory exists
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            // Move the uploaded file to the target directory
            if (!move_uploaded_file($file['tmp_name'], $image)) {
                $form->get('image')->setMessages(['File upload failed.']);
            }
        }

        if ($image != "") {
            $post->setImage($image);
        }

        $this->entityManager->flush();

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
                $post = $this->entityManager->getRepository(Post::class)->find($id);
                $this->entityManager->remove($post);
                $this->entityManager->flush();
            }

            return $this->redirect()->toRoute('post');
        }

        return [
            'id'    => $id,
            'post' => $this->entityManager->getRepository(Post::class)->find($id),
        ];
    }
}
