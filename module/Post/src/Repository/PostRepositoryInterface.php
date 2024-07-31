<?php


namespace Post\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Post\Entity\Post;
use User\Entity\User;

interface PostRepositoryInterface
{
    /**
     * PostRepositoryInterface constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager);

    /**
     * Get a list of posts with optional filtering and total count flag.
     *
     * @param bool $totalFlag - Flag to indicate if the total count is required.
     * @param string|null $filter - Optional filter to apply to the query.
     * @return QueryBuilder - The query builder object for retrieving posts.
     */
    public function getPostList(bool $totalFlag = false, string $filter = null): QueryBuilder;

    /**
     * Get a post by its ID.
     *
     * @param int $id - The ID of the post to retrieve.
     * @return Post|null - The post entity if found, null otherwise.
     */
    public function getPostById(int $id): ?Post;

    /**
     * Get a user by their ID.
     *
     * @param int $id - The ID of the user to retrieve.
     * @return User|null - The user entity if found, null otherwise.
     */
    public function getUserById(int $id): ?User;

    /**
     * Flush changes to the database.
     *
     * @return void
     */
    public function flush(): void;

    /**
     * Persist a post entity to the database.
     *
     * @param Post $post - The post entity to persist.
     * @return void
     */
    public function persist(Post $post): void;

    /**
     * Remove a post entity from the database.
     *
     * @param Post $post - The post entity to remove.
     * @return void
     */
    public function remove(Post $post): void;

    /**
     * Begin a database transaction.
     *
     * @return void
     */
    public function beginTransaction(): void;

    /**
     * Commit the current database transaction.
     *
     * @return void
     */
    public function commit(): void;

    /**
     * Rollback the current database transaction.
     *
     * @return void
     */
    public function rollback(): void;
}

