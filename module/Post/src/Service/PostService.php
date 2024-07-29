<?php

namespace Post\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Exception;
use InvalidArgumentException;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\JsonModel;
use Post\Entity\Post;
use Post\Form\PostForm;
use Post\Repository\PostRepository;
use User\Entity\User;

class PostService
{
    private PostRepository $postRepository;
    private int $limit;

    public function __construct( PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
        $this->limit = 10;
    }

    public function preparePosts(&$posts): void
    {
        foreach ($posts as &$post) {
            $createdAt = $post['created_at'];
            $timeService  = new TimeService($createdAt);
            $post['created_at'] = $timeService->dateToShamsi();

        }
    }

    public function calculateTotalPosts(): float
    {
        $totalItems = 0;
        try {
            $totalItems = $this->postRepository->getPostCount();
        } catch (NoResultException|NonUniqueResultException $e) {
            var_dump($e->getMessage());
        }
        return ceil($totalItems / $this->limit);
    }

    public function getPaginatedPosts($page, $limit): array
    {
        $this->limit = $limit;
        $offset = ($page - 1) * $this->limit;

        $posts = $this->postRepository->getPosts($offset, $this->limit);

        $this->preparePosts($posts);

        $totalPages = $this->calculateTotalPosts();

        return [
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ];
    }

//    public function setImage($fileData, $post): void
//    {
//        $image = "";
//        $file = $fileData['image'];
//        if ($file['error'] == UPLOAD_ERR_OK) {
//
//            $uploadDir = './public/img/';
//            $extension = pathinfo(basename($file['name']), PATHINFO_EXTENSION);
//            $newFileName = $post->getId() . '.' . $extension;
//            $image = $uploadDir . $newFileName;
//
//            if (!file_exists($uploadDir)) {
//                mkdir($uploadDir, 0777, true);
//            }
//
//            if (move_uploaded_file($file['tmp_name'], $image)) {
//                $post->setImage($image);
//                $this->postRepository->flush();
//            } else {
//                var_dump("Error uploading the file.");
//            }
//        }
//    }
//
//    public function setImageApi($data, $post): void
//    {
//        $post->setImage($data['image']);
//        $this->postRepository->flush();
//    }


    /**
     * @throws Exception
     */
    public function setImage($fileData, $post): void
    {
        $image = "";
        // Check if $fileData is an array or a api string
        if ( isset($fileData['image'])) {
            $file = $fileData['image'];
            if ($file['error'] == UPLOAD_ERR_OK) {
                $image = $this->handleImageUpload($file, $post);

            }
        } elseif (is_string($fileData) && $fileData) {
            $image = $this->handleApiImageUpload($fileData, $post);
        }

        if ($image) {
            $post->setImage($image);
            $this->postRepository->flush();
        }
    }

    private function handleImageUpload($file, $post)
    {
        $uploadDir = './public/img/';
        $extension = pathinfo(basename($file['name']), PATHINFO_EXTENSION);
        $newFileName = $post->getId() . '.' . $extension;
        $image = $uploadDir . $newFileName;

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $image)) {
            return $image;
        } else {
            throw new Exception("Error uploading the file.");
        }
    }

    private function handleApiImageUpload($apiString, $post)
    {
        $uploadDir = './public/img/';
        $extension = pathinfo(basename($apiString), PATHINFO_EXTENSION);
        $newFileName = $post->getId() . '.' . $extension;
        $image = $uploadDir . $newFileName;

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $data = $apiString;
        if ($data === false) {
            throw new Exception("Error decoding the api string.");
        }

        if (file_put_contents($image, $data) !== false) {
            return $image;
        } else {
            throw new Exception("Error saving the api image.");
        }
    }


    public function validatePostData($data): int
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
        return 1;
    }


    public function addPost($data, $fileData, $user): void
    {
        $this->validatePostData($data);

        $post = new Post();
        $post->setTitle($data["title"]);
        $post->setDescription($data["description"]);

        $postUser = $this->postRepository->getUserById($user->getId());
        $post->setUser($postUser);

        date_default_timezone_set("Asia/Tehran");
        $post->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));

        $this->postRepository->persist($post);
        $this->postRepository->flush();

        $this->setImage($fileData, $post);
    }

    public function getPostById($id)
    {
        return $this->postRepository->getPostById($id);
    }

    public function updatePost($post, $data, $fileData): void
    {
        $this->validatePostData($data);

        $post->setTitle($data["title"]);
        $post->setDescription($data["description"]);

        $this->postRepository->flush();
        $this->setImage($fileData, $post);
    }
    public function deletePost($post): void
    {
        $file_path = $post->getImage();
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $this->postRepository->remove($post);
        $this->postRepository->flush();
    }

    public function apiAddPost($data, $file)
    {

        try {
            if (!$this->validatePostData($data)) {
                return [
                    'success' => false,
                    'message' => 'Title, description, and user_id are required.'
                ];
            }
        }catch (InvalidArgumentException $ex){
            return json_decode($ex->getMessage(), true);
        }

        $user = $this->postRepository->getUserById($data['user_id']);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        $this->addPost($data, $data['image'], $user);

        return [
            'success' => true,
        ];

    }
}

