<?php

namespace Post\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

use Post\Entity\Post;

use User\Entity\User;


class PostRepository implements PostRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getPostList($totalFlag = false, $filter = null): QueryBuilder
    {
        $qb = $this->entityManager->getRepository(Post::class)->createQueryBuilder('p')
            ->leftJoin('p.user', 'u', 'u.id = p.user_id');

        if ($filter) {
            $qb->Where($filter);
        }

        if ($totalFlag) {
            $qb->select('COUNT(p.id)');
        } else {
            $qb->select('p.title, p.description, u.username, p.created_at, p.image, u.id AS user_id, p.id AS post_id');
            $qb->orderBy('p.created_at', 'ASC');
        }

        return $qb;
    }

    public function getPostById($id): ?Post
    {
        return $this->entityManager
            ->getRepository(Post::class)
            ->find($id);
    }

    public function getUserById($id): ?User
    {
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