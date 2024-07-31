<?php

namespace Post\Service;

use Comment\Entity\Comment;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use InvalidArgumentException;

use Post\Entity\Post;
use User\Entity\User;
use Post\Repository\PostRepository;
use Post\Repository\PostRepositoryInterface;

class PostService implements PostServiceInterface
{
    private PostRepositoryInterface $postRepository;
    private int $limit;

    public function __construct( PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
        $this->limit = 10;
    }

    public function preparePosts(&$posts): void
    {
        foreach ($posts as &$post) {
            $createdAt = $post['created_at'];
            $timeService  = new TimeService($createdAt);
            $post['created_at'] = $timeService->dateToShams();

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

    public function getPostsCount(): int|null
    {
        try {
            return $this->postRepository->getPostList(true)->getQuery()->getSingleScalarResult();

        } catch (NonUniqueResultException|NoResultException $e) {
            return null;
        }
    }

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

    private function  handleImageUpload($file, $post): string
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

    private function handleApiImageUpload($apiString, $post): string
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


    public function validatePostData($data): array
    {
        $validationStatus = [ 'success' => true, 'addCommentStatus' => false, 'userLoginStatus' => true, 'titleStatus' , 'descriptionStatus'  ];
        if (empty($data['title']) || strlen($data['title']) > 255) {
            $validationStatus['titleStatus'] = false;
        }else {
            $validationStatus['titleStatus'] = true;
        }

        if (empty($data['description']) || strlen($data['description']) > 255) {
            $validationStatus['descriptionStatus'] = false;
        }else {
            $validationStatus['descriptionStatus'] = true;
        }

        if (!$validationStatus['titleStatus'] || !$validationStatus['descriptionStatus']) {
            $validationStatus['success'] = false;
            throw new InvalidArgumentException(json_encode($validationStatus));
        }
        return $validationStatus;
    }

    public function addPost($data, $fileData, $user): array
    {
        $this->postRepository->beginTransaction();

        $addNewPostStatus = [
            'success' => false,
            'userLoginStatus' => true,
            'titleStatus' => false,
            'descriptionStatus' => false,
            'addCommentStatus' => false,
        ];
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

            $addNewPostStatus['titleStatus']  = true;
            $addNewPostStatus['descriptionStatus'] = true;
            $addNewPostStatus['success'] = true;
            $addNewPostStatus['addCommentStatus'] = true;
        }catch (\Exception $e) {

            $error = json_decode($e->getMessage(), true);
            $this->postRepository->rollback();

            $addNewPostStatus['addCommentStatus'] = $error['addCommentStatus'] ;
            $addNewPostStatus['titleStatus'] =  $error['titleStatus']  ;
            $addNewPostStatus['descriptionStatus'] =  $error['descriptionStatus'];
            $addNewPostStatus['success'] = $error['success'] ;

        }

        return $addNewPostStatus;




    }

    private function createComment(Post $post): array
    {
        try {
//            throw new \Exception('Simulated error');
            $comment = new Comment();
            $comment->setPostId($post->getId());
            $comment->setComment('Its good');
            date_default_timezone_set("Asia/Tehran");
            $comment->setCreatedAt(new \DateTime());

            $this->postRepository->persist($comment);
            $this->postRepository->flush();
            return [
                'userLoginStatus' => true,
                'titleStatus' => true,
                'descriptionStatus' => true,
                'success' => true,
                'addCommentStatus' => true,
            ];
        }
        catch (\Exception $e) {
            throw new InvalidArgumentException(json_encode( [
                'userLoginStatus' => true,
                'titleStatus' => true,
                'descriptionStatus' => true,
                'success' => false,
                'addCommentStatus' => false
            ] ));

        }


    }

    public function getPostById($id): ?Post
    {
        try {
            return $this->postRepository->getPostById($id);
        }catch (\Exception $e) {
            return null;
        }
    }

    public function updatePost($post, $data, $fileData): array
    {
        try {
            $updatePostStatus = $this->validatePostData($data);

            $post->setTitle($data["title"]);
            $post->setDescription($data["description"]);

            $this->postRepository->flush();
            $this->setImage($fileData, $post);
        }catch (\Exception $e) {
            $error = json_decode($e->getMessage(), true);

            $updatePostStatus['addCommentStatus'] = $error['addCommentStatus'] ;
            $updatePostStatus['titleStatus'] =  $error['titleStatus']  ;
            $updatePostStatus['descriptionStatus'] =  $error['descriptionStatus'];
            $updatePostStatus['success'] = $error['success'] ;
            return $updatePostStatus;
        }
        return $updatePostStatus;
    }
    public function deletePost($post): bool
    {
        try {
            $file_path = $post->getImage();
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $this->postRepository->remove($post);
            $this->postRepository->flush();
            return 1;
        }
        catch (\Exception $e) {
            return 0;
        }
    }

    public function getUserById($id): ?User
    {
        try {
            return $this->postRepository->getUserById($id);
        }
        catch (\Exception $e) {
            return null;
        }
    }
}

