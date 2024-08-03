<?php


namespace Post\view\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\Authentication\AuthenticationService;

class MenuBar extends AbstractHelper
{
    protected $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke()
    {

        $user = $this->authService->getIdentity();
        $isLoggedIn = $user !== null;

        $menuItems = [
            'Home' => '/',
            'Post' => '/post',
        ];

//        if ($isLoggedIn) {
        $menuItems['Logout'] = '/logout';
        $menuItems['Profile'] = '/profile';
//        } else {
        $menuItems['Login'] = '/login';
        $menuItems['Register'] = '/register';
//        }

        $html = '<ul class="menu-bar">';
        foreach ($menuItems as $name => $link) {
            $html .= sprintf('<li><a href="%s">%s</a></li>', $link, $name);
        }
        $html .= '</ul>';

        return $html;
    }
}
