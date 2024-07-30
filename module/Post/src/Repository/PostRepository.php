<?php

namespace Post\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Post\Entity\Post;
use User\Entity\User;
use Comment\Entity\Comment;

class PostRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    private function createBaseQuery(): QueryBuilder
    {
        return $this->entityManager->getRepository(Post::class)->createQueryBuilder('p')
            ->leftJoin('p.user', 'u', 'u.id = p.user_id')
            ->select('p.title, p.description, u.username, p.created_at, p.image, u.id AS user_id, p.id AS post_id')
            ->orderBy('p.created_at', 'ASC');
    }
    public function getPosts($offset , $limit)
    {
        $queryBuilder = $this->createBaseQuery()->setFirstResult($offset)
            ->setMaxResults($limit);;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getPostCount(): float|bool|int|string|null
    {
        $queryBuilder = $this->createBaseQuery();

        $queryBuilder->select('COUNT(p.id)');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function getPostById($id)
    {
        return $this->entityManager
            ->getRepository(Post::class)
            ->find($id);
    }

    public function getUserById($id){
        return $this->entityManager
            ->getRepository(User::class)
            ->find($id);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }

    public function persist($post): void
    {
        $this->entityManager->persist($post);
    }

    public function remove($post): void
    {
        $this->entityManager->remove($post);
    }

    public function beginTransaction(): void
    {
        $this->entityManager->beginTransaction();
    }

    public function commit(): void
    {
        $this->entityManager->commit();
    }

    public function rollback(): void
    {
        $this->entityManager->rollback();
    }



}