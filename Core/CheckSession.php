<?php

namespace Core;

use \core\Router;

class CheckSession
{
    /**
     * Preventing access to non-authenticated user
     *
     * @return void
     */
    static public function SessionAuth()
    {
        if (
            isset($_SESSION['fingerPrint']) &&
            $_SESSION['fingerPrint'] === md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'])
        ) {
            return true;
        } else {
            Router::redirect('public/home/index');
        }
    }

    /**
     * Preventing access to non-administrator user
     *
     * @param  string $route
     * @return void
     */
    static public function isAdminRoute(string $route)
    {
        if (isset($_SESSION['roleId']) && $_SESSION['roleId'] != 2) {
            Router::redirect($route);
        }
    }

    /**
     * Redirect authenticated user (an auth user should not be able to access login or register page)
     *
     * @param  string $route
     * @return void
     */
    static public function redirectLoggedInUser(string $route)
    {
        if (isset($_SESSION['userId'])) {
            Router::redirect($route);
        }
    }
}
