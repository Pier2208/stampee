<?php

namespace App\Controllers;

use \Core\View;
use \Core\CheckSession;
use \Core\Validation;
use \Core\Router;
use \App\Models\Country;
use \App\Models\Condition;
use \App\Models\Category;
use \App\Models\StampDAO;
use \App\Models\Theme;

class Stamp extends \Core\Controller
{

    /**
     * PRIVATE ROUTE
     * pages de timbres d'un utilisateur
     * http://localhost:8888/stampee/public/profile/index
     *
     * @return void
     */
    public function indexAction()
    {

        CheckSession::SessionAuth();

        // metatags
        $meta['title'] = "Mes timbres | Stampee";

        // Récupérer tous les timbres de l'utilisateur
        $allStamps = StampDAO::getAllStampsByUser($_SESSION["userId"]);

        // view
        return View::renderTemplate('Stamp/index.twig', [
            'meta' => $meta,
            'stamps' => $allStamps
        ]);
    }

    /**
     * PRIVATE ROUTE
     * page formulaire de création d'un timbre
     * http://localhost:8888/stampee/public/stamp/add
     *
     * @return void
     */
    public function addAction()
    {

        CheckSession::SessionAuth();

        // metatags
        $meta['title'] = "Ajouter un timbre | Stampee";

        // populer select country
        $selectCountries = Country::getAll();

        // populer select condition
        $selectConditions = Condition::getAll();

        // populer select category
        $selectCategories = Category::getAll();

        // populer select theme
        $selectThemes = Theme::getAll();

        // errors
        $errors = [];

        if (isset($_REQUEST['submit'])) {
            $val = new Validation;

            $val->name('name')->value($_REQUEST['name'])->required();
            $val->name('description')->value($_REQUEST['description'])->required();
            $val->name('country')->value($_REQUEST['country'])->required();
            $val->name('category')->value($_REQUEST['category'])->required();
            $val->name('theme')->value($_REQUEST['theme'])->required();
            $val->name('state')->value($_REQUEST['state'])->required();
            $val->name('year')->value($_REQUEST['year'])->pattern('int')->required();
            $val->name('width')->value($_REQUEST['width'])->pattern('int')->required();
            $val->name('height')->value($_REQUEST['height'])->pattern('int')->required();

            if (!$val->isSuccess()) {
                $errors = $val->getErrors();
            } else {
                // if <select> multiple, first image would be accessible on $_FILES['file']['tmp_name'][0]
                $success = StampDAO::create($_REQUEST, $_FILES['file']['tmp_name']);

                //rediriger à la page des timbres
                if ($success)
                    Router::redirect('public/stamp/index');
            }
        }
        // view
        return View::renderTemplate('Stamp/add.twig', [
            'meta' => $meta,
            'countries' => $selectCountries,
            'conditions' => $selectConditions,
            'categories' => $selectCategories,
            'themes' => $selectThemes,
            'stamp' => $_REQUEST,
            'errors' => $errors
        ]);
    }

    /**
     * PRIVATE ROUTE
     * page formulaire d'édition d'un timbre populé
     * http://localhost:8888/stampee/public/stamp/select?stamp=:id
     *
     * @return void
     */
    public function selectAction()
    {

        CheckSession::SessionAuth();

        $stampId = $_GET["stamp"];

        // metatags
        $meta['title'] = "Editer un timbre | Stampee";

        // get stamp by id
        $stamp = StampDAO::getStampById($stampId);

        // rediriger user non propriétaire du timbre
        if ($stamp["user_id"] !== $_SESSION["userId"])
            Router::redirect("public/gallery/index");

        // populer select country
        $selectCountries = Country::getAll();

        // populer select condition
        $selectConditions = Condition::getAll();

        // populer select category
        $selectCategories = Category::getAll();

        // populer select theme
        $selectThemes = Theme::getAll();

        // view
        return View::renderTemplate('Stamp/add.twig', [
            'meta' => $meta,
            'countries' => $selectCountries,
            'conditions' => $selectConditions,
            'categories' => $selectCategories,
            'themes' => $selectThemes,
            'stamp' => $stamp
        ]);
    }

    /**
     * PRIVATE ROUTE
     * route de mise à jour d'un timbre
     * http://localhost:8888/stampee/public/stamp/update
     */
    public function updateAction()
    {
        CheckSession::SessionAuth();

        // metatags
        $meta['title'] = "Editer un timbre | Stampee";

        // populer select country
        $selectCountries = Country::getAll();

        // populer select condition
        $selectConditions = Condition::getAll();

        // populer select category
        $selectCategories = Category::getAll();

        // populer select theme
        $selectThemes = Theme::getAll();

        // data validation
        if (isset($_REQUEST['submit'])) {
            $val = new Validation;

            $val->name('name')->value($_REQUEST['name'])->required();
            $val->name('description')->value($_REQUEST['description'])->required();
            $val->name('country')->value($_REQUEST['country'])->required();
            $val->name('category')->value($_REQUEST['category'])->required();
            $val->name('theme')->value($_REQUEST['theme'])->required();
            $val->name('state')->value($_REQUEST['state'])->required();
            $val->name('year')->value($_REQUEST['year'])->pattern('int')->required();
            $val->name('width')->value($_REQUEST['width'])->pattern('int')->required();
            $val->name('height')->value($_REQUEST['height'])->pattern('int')->required();

            // get the stamp image or empty string and create an associative array
            $image = ["img" => $_FILES['file']['tmp_name'] ?? ''];

            if (!$val->isSuccess()) {
                $errors = $val->getErrors();
                // view
                return View::renderTemplate('Stamp/add.twig', [
                    'meta' => $meta,
                    'countries' => $selectCountries,
                    'conditions' => $selectConditions,
                    'categories' => $selectCategories,
                    'themes' => $selectThemes,
                    'errors' => $errors,
                    'stamp' => $_REQUEST,
                    'url' => $_REQUEST['url'],
                    'public_id' => $_REQUEST['public_id']
                ]);
            } else {
                // merge all stamp data array with image array
                $newStamp = array_merge($_REQUEST, $image);

                $success = StampDAO::update($newStamp);

                //rediriger à la page des timbres
                if ($success)
                    Router::redirect('public/stamp/index');
            }
        }
    }

    /**
     * PRIVATE ROUTE
     * route de suppression d'un timbre
     * http://localhost:8888/stampee/public/stamp/delete?stamp=:id
     */

    public function deleteAction()
    {
        CheckSession::SessionAuth();

        // récupérer l'id du timbre dans la query string
        $stampId = $_GET["stamp"];

        // rediriger un user non propriétaire du timbre
        $stamp = StampDAO::getStampById($stampId);
        if ($stamp["user_id"] !== $_SESSION["userId"])
            Router::redirect('public/gallery/index');

        // requête de supression du timbre (l'image associée à ce timbre dans la table Image est 
        // automatiquement supprimée du fait du ON DELETE CASCADE)
        $success = StampDAO::delete($stampId);

        // on redirige vers la page de timbres de cet utilisateur
        if ($success)
            Router::redirect('public/stamp/index');
    }
}
