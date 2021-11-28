<?php

namespace App\Controllers;

use \Core\Router;

class Auth extends \Core\Controller
{
    /**
     * route: http://localhost:8888/stampee/public/auth/logout
     * logout user
     */
    public function logout()
    {
        session_destroy();
        Router::redirect('public/home/index');
    }
}
