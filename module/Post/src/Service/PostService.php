<?php

namespace Post\Service;

use Post\Form\PostForm;

class PostService
{
    private $postForm;
    public function __construct(PostForm $postForm)
    {
        $this->postForm = $postForm;
    }
    public function isValid(): bool
    {
        return $this->postForm->isValid();
    }
}