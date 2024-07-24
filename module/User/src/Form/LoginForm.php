<?php

namespace User\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;

class LoginForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('login');

        $this->add([
            'name' => 'username',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Username',
            ],
        ]);

        $this->add([
            'name' => 'password',
            'type' => Element\Password::class,
            'options' => [
                'label' => 'Password',
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Element\Submit::class,
            'attributes' => [
                'value' => 'Login',
                'id' => 'submitbutton',
            ],
        ]);
    }
}
