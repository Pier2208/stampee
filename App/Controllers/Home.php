<?php

namespace App\Controllers;

use \Core\View;
use \Core\Validation;
use \Core\Router;
use \Core\CheckSession;
use \App\Models\User;
use \App\Models\ProfileDAO;
use \App\Models\Filters;

class Home extends \Core\Controller
{

    /**
     * PUBLIC ROUTE
     * Show the index page (register form)
     * http://localhost:8888/stampee/public/home/index
     *
     * @return void
     */
    public function indexAction()
    {
        // si le user est connecté, rediriger le d'où il vient
        CheckSession::redirectLoggedInUser('public/gallery/index');

        // metatags
        $meta['title'] = "Bienvenue | Créer un compte sur Stampee";
        $meta['description'] = "Créer un compte sur Stampee pour profiter de tous nos avantages.";

        // errors
        $errors = [];

        // data validation
        if (isset($_REQUEST['submit'])) {
            $val = new Validation;
            $val->name('username')->value($_REQUEST['username'])->required();
            $val->name('email')->value($_REQUEST['email'])->pattern('email')->required();
            $val->name('password')->value($_REQUEST['password'])->min(8)->required();
            $val->name('confirmPassword')->value($_REQUEST['confirmPassword'])->equal($_REQUEST['password'])->required();

            // populate $errors[] if any
            if (!$val->isSuccess()) {
                $errors = $val->getErrors();
            } else {
                // hash password
                $options = [
                    'cost' => 10,
                ];
                $hashPassword = password_hash($_REQUEST['password'], PASSWORD_BCRYPT, $options);
                $_REQUEST['password'] = $hashPassword;

                // create new user
                $result = User::register($_REQUEST);

                // if back-end errors (existing username or email in the db), populate $errors[]
                if (!$result["success"]) {
                    $errors = $result;
                } else {
                    // create a profile linked to the user
                    $success = ProfileDAO::create($result["userId"]);

                    if ($success) {
                        // create default filters associated to this profile
                        $success = Filters::create($result["userId"]);

                        // rediriger vers le formulaire de login
                        if ($success)
                            Router::redirect('public/home/login');
                    }
                }
            }
        }
        // view
        return View::renderTemplate('Home/index.twig', [
            'meta' => $meta,
            'user' => $_POST,
            'errors' => $errors
        ]);
    }

    /**
     * PUBLIC ROUTE
     * Show the index page (login)
     * http://localhost:8888/stampee/public/home/login
     *
     * @return void
     */
    public function loginAction()
    {

        // si le user est connecté, rediriger le d'où il vient
        CheckSession::redirectLoggedInUser('public/auction/index');

        // metatags
        $meta['title'] = "Bienvenue | Connectez-vous à votre compte Stampee";
        $meta['description'] = "Connectez-vous à votre compte Stampee et accèdez à toutes vos enchères.";

        // errors
        $errors = [];

        // data validation
        if (isset($_REQUEST['submit'])) {
            $val = new Validation;
            $val->name('username')->value($_REQUEST['username'])->required();
            $val->name('password')->value($_REQUEST['password'])->required();

            // populate $errors[] if any
            if (!$val->isSuccess()) {
                $errors = $val->getErrors();
            } else {
                $loginResult = User::login($_REQUEST);

                if (!$loginResult['success']) {
                    $errors = $loginResult;
                } else {
                    // rediriger vers la galerie des enchères
                    Router::redirect('public/gallery/index');
                }
            }
        }
        // view
        return View::renderTemplate('Home/login.twig', [
            'meta' => $meta,
            'user' => $_POST,
            'errors' => $errors
        ]);
    }
}
