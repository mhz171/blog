<?php

namespace Post\Controller;


use InvalidArgumentException;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Paginator\Adapter\ArrayAdapter;
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

    public function __construct(PostService $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function indexAction()
    {
        try {
            $page = $this->params()->fromQuery('page', 1);
            $limit = 5;

            $paginator = $this->serviceManager->getPaginatedPosts($page, $limit);

            return new ViewModel([
                'paginator' => $paginator,
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
            $this->serviceManager->addPost($data, $fileData);
            return $this->redirect()->toRoute('post');
        } catch (InvalidArgumentException $ex) {
            // Add error message to form
            $errors = json_decode($ex->getMessage(), true);
            $form->setMessages($errors);
        }
        return ['form' => $form];

    }

    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('post', ['action' => 'add']);
        }

        $form = new PostForm();
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        $data = ArrayUtils::iteratorToArray($request->getPost());

        $viewData = ['id' => $id, 'form' => $form];

        if (!$request->isPost()) {
            return $viewData;
        }

        try {
            $post = $this->entityManager->getRepository(Post::class)->find($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('post', ['action' => 'index']);
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
        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('post');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int)$request->getPost('id');
                $post = $this->entityManager->getRepository(Post::class)->find($id);

                $file_path = $post->getImage();
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                $this->entityManager->remove($post);
                $this->entityManager->flush();
            }

            return $this->redirect()->toRoute('post');
        }

        return [
            'id' => $id,
            'post' => $this->entityManager->getRepository(Post::class)->find($id),
        ];
    }
}
