<?php

namespace Post\Service;

use Comment\Entity\Comment;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use InvalidArgumentException;

use Post\Repository\PostRepositoryInterface;
use Post\Entity\Post;
use User\Entity\User;


class PostService implements PostServiceInterface
{
    //connection with db
    private PostRepositoryInterface $postRepository;

    //limit for post in one page
    private int $limit;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
        //set default to 10 post in one page
        $this->limit = 10;
    }

    public function preparePosts(&$posts): void
    {
        //convert date from miladi to shamsi
        foreach ($posts as &$post) {
            $createdAt = $post['created_at'];
            $timeService = new TimeService($createdAt);
            $post['created_at'] = $timeService->dateToShams();

        }
    }


    public function calculateTotalPosts($totalPosts): float
    {
        return ceil($totalPosts / $this->limit);
    }

    public function getPosts($offset): array
    {
        //reload post from db
        return $this->postRepository->getPostList(false)->setFirstResult($offset)
            ->setMaxResults($this->limit)->getQuery()->getResult();
    }

    public function getPostsCount(): int|null
    {
        //get number of all post available in db
        try {
            return $this->postRepository->getPostList(true)->getQuery()->getSingleScalarResult();

        } catch (NonUniqueResultException|NoResultException $e) {
            return null;
        }
    }

    public function getPaginatedPosts($page, $limit): array
    {
        // limit for number of post in one page
        $this->limit = $limit;
        $offset = ($page - 1) * $this->limit;

        $posts = $this->getPosts($offset);

        $totalPosts = $this->getPostsCount();

        $this->preparePosts($posts);

        $totalPages = $this->calculateTotalPosts($totalPosts);
        //return controller to show in view
        return [
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ];
    }

    public function setImage($fileData, $post): void
    {

        $image = "";
        // if image in file data is set it's from view else from api and its string
        if (isset($fileData['image'])) {
            $file = $fileData['image'];
            if ($file['error'] == UPLOAD_ERR_OK) {
                //image from ui
                $image = $this->handleImageUpload($file, $post);

            }
        } elseif (is_string($fileData) && $fileData) {
            //image from api
            $image = $this->handleApiImageUpload($fileData, $post);
        }
        //if upload image set the image
        if ($image) {
            $post->setImage($image);
            $this->postRepository->flush();
        }
    }

    private function handleImageUpload($file, $post): string
    {
        //set location to want save image
        $uploadDir = './public/img/';
        //find extension
        $extension = pathinfo(basename($file['name']), PATHINFO_EXTENSION);
        // set name file like : postId.extension
        // example 150.jpg
        $newFileName = $post->getId() . '.' . $extension;
        //set full address
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
        //validation status most have success, comment status, user login status, title status and description status
        $validationStatus = [
            'success' => true,
            'addCommentStatus' => false,
            'userLoginStatus' => true,
            'titleStatus',
            'descriptionStatus'
        ];
        //not valid
        if (empty($data['title']) || strlen($data['title']) > 255) {
            $validationStatus['titleStatus'] = false;
        } else {// is valid
            $validationStatus['titleStatus'] = true;
        }
        //not valid
        if (empty($data['description']) || strlen($data['description']) > 255) {
            $validationStatus['descriptionStatus'] = false;
        } else { //is valid
            $validationStatus['descriptionStatus'] = true;
        }
        //if title and description is not valid means success is false
        if (!$validationStatus['titleStatus'] || !$validationStatus['descriptionStatus']) {
            $validationStatus['success'] = false;
            throw new InvalidArgumentException(json_encode($validationStatus));
        }
        return $validationStatus;
    }

    public function addPost($data, $fileData, $user): array
    {
        //start transaction for add post and comment together
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

            //validation data
            $this->validatePostData($data);

            // create new post
            $post = new Post();
            $post->setTitle($data["title"]);
            $post->setDescription($data["description"]);
            // find user for set post
            $postUser = $this->postRepository->getUserById($user->getId());
            $post->setUser($postUser);

            date_default_timezone_set("Asia/Tehran");
            $post->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));

            //add post to db
            $this->postRepository->persist($post);
            $this->postRepository->flush();

            $this->setImage($fileData, $post);
            // set comment for this post
            $this->createComment($post);

            // end transaction
            $this->postRepository->commit();

            //set all status to true
            $addNewPostStatus['titleStatus'] = true;
            $addNewPostStatus['descriptionStatus'] = true;
            $addNewPostStatus['success'] = true;
            $addNewPostStatus['addCommentStatus'] = true;
        } catch (\Exception $e) {
            // we have error and set status for this error
            $error = json_decode($e->getMessage(), true);
            $this->postRepository->rollback();

            $addNewPostStatus['addCommentStatus'] = $error['addCommentStatus'];
            $addNewPostStatus['titleStatus'] = $error['titleStatus'];
            $addNewPostStatus['descriptionStatus'] = $error['descriptionStatus'];
            $addNewPostStatus['success'] = $error['success'];

        }

        return $addNewPostStatus;


    }

    private function createComment(Post $post): array
    {
        try {
//            throw new \Exception('Simulated error');
            // create new comment
            $comment = new Comment();
            $comment->setPostId($post->getId());
            $comment->setComment('Its good');
            date_default_timezone_set("Asia/Tehran");
            $comment->setCreatedAt(new \DateTime());

            // add to db
            $this->postRepository->persist($comment);
            $this->postRepository->flush();

            //return success status
            return [
                'userLoginStatus' => true,
                'titleStatus' => true,
                'descriptionStatus' => true,
                'success' => true,
                'addCommentStatus' => true,
            ];
        } catch (\Exception $e) {
            // return status
            throw new InvalidArgumentException(json_encode([
                'userLoginStatus' => true,
                'titleStatus' => true,
                'descriptionStatus' => true,
                'success' => false,
                'addCommentStatus' => false
            ]));

        }


    }

    public function getPostById($id): ?Post
    {
        try {
            return $this->postRepository->getPostById($id);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function updatePost($post, $data, $fileData): array
    {
        try {
            // check validation
            $updatePostStatus = $this->validatePostData($data);

            $post->setTitle($data["title"]);
            $post->setDescription($data["description"]);
            // update title and description
            $this->postRepository->flush();

            //update image
            $this->setImage($fileData, $post);
        } catch (\Exception $e) {
            // set status
            $error = json_decode($e->getMessage(), true);

            $updatePostStatus['addCommentStatus'] = $error['addCommentStatus'];
            $updatePostStatus['titleStatus'] = $error['titleStatus'];
            $updatePostStatus['descriptionStatus'] = $error['descriptionStatus'];
            $updatePostStatus['success'] = $error['success'];
            return $updatePostStatus;
        }
        //return status
        return $updatePostStatus;
    }

    public function deletePost($post): bool
    {
        try {
            // delete image
            $file_path = $post->getImage();
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            // delete data in db
            $this->postRepository->remove($post);
            $this->postRepository->flush();
            // return true
            return 1;
        } catch (\Exception $e) {
            //if cant delete return false
            return 0;
        }
    }

    public function getUserById($id): ?User
    {
        try {
            return $this->postRepository->getUserById($id);
        } catch (\Exception $e) {
            return null;
        }
    }
}

