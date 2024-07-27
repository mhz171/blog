<?php

namespace Post\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use InvalidArgumentException;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Post\Entity\Post;
use Post\Form\PostForm;
use User\Entity\User;

class PostService
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getQuery()
    {
        return $this->entityManager->getRepository(Post::class)->createQueryBuilder('p')
            ->leftJoin('p.user', 'u', 'u.id = p.user_id')
            ->select('p.title, p.description, u.username, p.created_at, p.image, u.id AS user_id, p.id AS post_id')
            ->orderBy('p.created_at', 'ASC')
            ->getQuery();
    }

    public function getPaginatedPosts($page, $limit)
    {

        $query = $this->getQuery();


        $doctrinePaginator = new DoctrinePaginator($query);
        $paginator = new Paginator(new ArrayAdapter(iterator_to_array($doctrinePaginator)));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($limit);



        return $paginator;
    }

    public function setImage($fileData, $post)
    {
        $image = "";
        if ($fileData['image']['error'] == UPLOAD_ERR_OK) {
            // Handle file upload
            $file = $fileData['image'];

            $uploadDir = './public/img/';
            $extension = pathinfo(basename($file['name']), PATHINFO_EXTENSION);
            $newFileName = $post->getId() . '.' . $extension;
            $image = $uploadDir . $newFileName;

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $image)) {
                // Update the post with the new image path
                $post->setImage($image);
                $this->entityManager->flush(); // Save the updated post
            } else {
                // Handle file upload error
                var_dump("Error uploading the file.");
            }
        }
    }
    private function validatePostData($data)
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'][] = "Title is required";
        } elseif (strlen($data['title']) > 255) {
            $errors['title'][] = "Title cannot exceed 255 characters";
        }

        if (empty($data['description'])) {
            $errors['description'][] = "Description is required";
        }elseif (strlen($data['description']) > 255) {
            $errors['description'][] = "description cannot exceed 255 characters";
        }

        if (!empty($errors)) {
            throw new InvalidArgumentException(json_encode($errors));
        }
    }

    public function addPost($data, $fileData,$user)
    {
        $this->validatePostData($data);
        $post = new Post();
        $post->setTitle($data["title"]);
        $post->setDescription($data["description"]);
        $post->setUser($this->entityManager->getRepository(User::class)->find($user->getId()));
        date_default_timezone_set("Asia/Tehran");
        $post->setCreatedAt(\DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        $this->setImage($fileData, $post);


    }

    public function getPostById($id)
    {
        try {
            return $this->entityManager->getRepository(Post::class)->find($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('post', ['action' => 'index']);
        }
    }

    public function updatePost($post, $data, $fileData){

        $this->validatePostData($data);

        $post->setTitle($data["title"]);
        $post->setDescription($data["description"]);

        $this->entityManager->flush();

        $this->setImage($fileData, $post);
    }
    public function deletePost($post)
    {
        $file_path = $post->getImage();
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }
}

