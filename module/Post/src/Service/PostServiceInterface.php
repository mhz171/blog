<?php

namespace Post\Service;

use Post\Entity\Post;
use Post\Repository\PostRepositoryInterface;
use User\Entity\User;

interface PostServiceInterface
{
    /**
     * PostServiceInterface constructor.
     *
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(PostRepositoryInterface $postRepository);

    /**
     * Prepare posts by formatting their created_at date.
     *
     * @param array $posts - Array of posts to prepare.
     * @return void
     */
    public function preparePosts(array &$posts): void;

    /**
     * Calculate total number of pages for posts.
     *
     * @param int $totalPosts - Total number of posts.
     * @return float - Total number of pages.
     */
    public function calculateTotalPosts(int $totalPosts): float;

    /**
     * Get posts with pagination.
     *
     * @param int $offset - Offset for pagination.
     * @return array - Array of posts.
     */
    public function getPosts(int $offset): array;

    /**
     * Get the total count of posts.
     *
     * @return int|null - Total count of posts or null if not found.
     */
    public function getPostsCount(): ?int;

    /**
     * Get paginated posts.
     *
     * @param int $page - Current page number.
     * @param int $limit - Number of posts per page.
     * @return array - Array containing paginated posts and pagination info.
     */
    public function getPaginatedPosts(int $page, int $limit): array;

    /**
     * Set the image for a post.
     *
     * @param array|string $fileData - File data for the image.
     * @param Post $post - The post entity.
     * @return void
     */
    public function setImage(array|string $fileData, Post $post): void;

    /**
     * Validate post data.
     *
     * @param array $data - Data to validate.
     * @return array - Validation status.
     */
    public function validatePostData(array $data): array;

    /**
     * Add a new post.
     *
     * @param array $data - Post data.
     * @param array|string $fileData - File data for the image.
     * @param User $user - The user creating the post.
     * @return array - Status of the post addition.
     */
    public function addPost(array $data, array|string $fileData, User $user): array;

    /**
     * Get a post by its ID.
     *
     * @param int $id - The ID of the post.
     * @return Post|null - The post entity or null if not found.
     */
    public function getPostById(int $id): ?Post;

    /**
     * Update an existing post.
     *
     * @param Post $post - The post entity to update.
     * @param array $data - New data for the post.
     * @param array|string $fileData - File data for the image.
     * @return array - Status of the post update.
     */
    public function updatePost(Post $post, array $data, array|string $fileData): array;

    /**
     * Delete a post.
     *
     * @param Post $post - The post entity to delete.
     * @return bool - Status of the deletion.
     */
    public function deletePost(Post $post): bool;

    /**
     * Get a user by their ID.
     *
     * @param int $id - The ID of the user.
     * @return User|null - The user entity or null if not found.
     */
    public function getUserById(int $id): ?User;
}

