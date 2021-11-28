<?php

namespace App\Controllers;

use \Core\View;
use \Core\CheckSession;
use \App\Models\ProfileDAO;
use \App\Models\Country;
use \Core\Validation;
use \Core\Router;

class Profile extends \Core\Controller
{

    /**
     * PRIVATE ROUTE
     * page de profil
     * http://localhost:8888/stampee/public/profile/index
     *
     * @return void
     */
    public function indexAction()
    {

        CheckSession::SessionAuth();

        // metatags
        $meta['title'] = "Mon profil | Stampee";
        $meta['description'] = "Rechercher parmi des centaines d'enchères.";

        // populer select country
        $selectCountries = Country::getAll();

        $profile = ProfileDAO::getProfileById((int)$_SESSION["userId"]);

        // errors
        $errors = [];

        if (isset($_REQUEST['submit'])) {
            $val = new Validation;

            $val->name('firstname')->value($_REQUEST['firstname'])->required();
            $val->name('lastname')->value($_REQUEST['lastname'])->required();
            $val->name('country')->value($_REQUEST['country'])->required();
            $val->name('dob')->value($_REQUEST['dob'])->required();

            $profileImg = ["img" => $_FILES['file']['error'] === UPLOAD_ERR_OK ? $_FILES['file']['tmp_name'] : ''];

            if (!$val->isSuccess()) {
                $errors = $val->getErrors();
            } else {
                $update = array_merge($_REQUEST, ["public_id" => $profile['public_id']], $profileImg);
                $success = ProfileDAO::update($update);

                //rediriger à la page de profil
                if ($success || is_null($success))
                Router::redirect('public/profile/index');
            }
        }

        // view
        return View::renderTemplate('Profile/index.twig', [
            'meta' => $meta,
            'profile' => $profile,
            "countries" => $selectCountries,
            'errors' => $errors
        ]);
    }
}
