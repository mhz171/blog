<?php

namespace Post\Repository;

use Doctrine\ORM\EntityManagerInterface;

class PostRepository
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }


}