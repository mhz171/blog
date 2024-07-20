<?php

namespace Post\Service;

use Post\Model\Post;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterInterface;

class PostService
{
    private $post;

    public function __construct(Post $post)
    {
        $this->postTable = $post;
    }

    public function validatePostData()
    {
        return 1;
//        $post = new Post();
//        $form->setInputFilter($post->getInputFilter());
//        $form->setData($data);
//
//        if (!$form->isValid()) {
//            return ['status' => 'error', 'messages' => $form->getMessages()];
//        }
//
//        $post->exchangeArray($form->getData());
//        return ['status' => 'success', 'post' => $post];
    }
}
