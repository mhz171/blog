<?php

namespace Post\Service;

use Comment\Entity\Comment;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use InvalidArgumentException;

use Post\Entity\Post;
use Post\Repository\PostRepository;

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

    public function calculateTotalPosts($totalPosts): float
    {
        return ceil($totalPosts / $this->limit);
    }

    public function getPosts($offset): array
    {
        return $this->postRepository->getPostList(false)->setFirstResult($offset)
            ->setMaxResults($this->limit)->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getPostsCount(): float|bool|int|string|null
    {
        return $this->postRepository->getPostList(true)->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getPaginatedPosts($page, $limit): array
    {
        $this->limit = $limit;
        $offset = ($page - 1) * $this->limit;

        $posts = $this->getPosts($offset);

        $totalPosts = $this->getPostsCount();

        $this->preparePosts($posts);

        $totalPages = $this->calculateTotalPosts($totalPosts);

        return [
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ];
    }


    /**
     * @throws Exception
     */
    public function setImage($fileData, $post): void
    {
        $image = "";
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
        $this->postRepository->beginTransaction();

        try {
//            throw new \Exception('Simulated error');
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

            $this->createComment($post);

            $this->postRepository->commit();
        }catch (\Exception $e) {

            $this->postRepository->rollback();

            throw $e;
        }

    }

    private function createComment(Post $post): void
    {
//        throw new \Exception('Simulated error');
        $comment = new Comment();
        $comment->setPostId($post->getId());
        $comment->setComment('Its good');
        date_default_timezone_set("Asia/Tehran");
        $comment->setCreatedAt(new \DateTime());

        $this->postRepository->persist($comment);
        $this->postRepository->flush();

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
            'message' => 'success'
        ];

    }
}

