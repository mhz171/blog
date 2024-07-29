<?php

namespace Post\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Post\Entity\Post;

class PostRepository
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    private function createBaseQuery()
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

    public function getPostCount()
    {
        $queryBuilder = $this->createBaseQuery();

        $queryBuilder->select('COUNT(p.id)');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }




}