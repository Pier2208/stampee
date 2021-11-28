<?php

namespace App\Controllers;

use App\Models\StampDAO;
use App\Models\AuctionDAO;
use \Core\View;
use \Core\Router;
use \Core\CheckSession;
use \Core\Validation;
use \App\Functions\Utils;
use \App\Models\Offer;

class Auction extends \Core\Controller
{

    /**
     * PRIVATE ROUTE
     * pages des enchères d'un utilisateur
     * http://localhost:8888/stampee/public/auction/index
     *
     * @return void
     */
    public function indexAction()
    {

        CheckSession::SessionAuth();

        // metatags
        $meta['title'] = "Mes enchères | Stampee";

        // Récupérer toutes les enchères de l'utilisateur
        $allAuctions = AuctionDAO::getAllAuctionsByUser($_SESSION["userId"]);

        // calculer le status de l'enchère
        foreach ($allAuctions as &$auction) {
            $auctionStatus = Utils::auctionStatus($auction["start_date"], $auction["end_date"]);
            $auction["status"] = $auctionStatus["msg"];
            $auction["statusKey"] = $auctionStatus["value"];

            // récupérer le montant de la dernière offre si elle existe
            $offers = Offer::getAll($auction["auctionId"]);
            if (count($offers) > 0) {
                $auction["current_price"] = $offers[0]["current_price"];
            }
        }

        // view
        return View::renderTemplate('Auction/index.twig', [
            'meta' => $meta,
            'auctions' => $allAuctions
        ]);
    }

    /**
     * PRIVATE ROUTE
     * page formulaire de création d'une enchère
     * http://localhost:8888/stampee/public/auction/add
     *
     * @return void
     */
    public function addAction()
    {

        CheckSession::SessionAuth();

        // metatags
        $meta['title'] = "Ajouter une enchère | Stampee";

        // populer select stamps
        $selectStamps = StampDAO::getAllStampsByUser($_SESSION["userId"]);

        // errors
        $errors = [];

        if (isset($_REQUEST['submit'])) {
            $val = new Validation;

            //$val->name('stamps[]')->value($_REQUEST['stamps'])->required();
            $val->name('name')->value($_REQUEST['name'])->required();
            $val->name('description')->value($_REQUEST['description'])->required();
            $val->name('start_date')->value($_REQUEST['start_date'])->required();
            $val->name('end_date')->value($_REQUEST['end_date'])->required();
            $val->name('start_price')->value($_REQUEST['start_price'])->required();

            if (!$val->isSuccess()) {
                $errors = $val->getErrors();
            } else {
                $success = AuctionDAO::create($_REQUEST);

                //rediriger à la page des enchères de l'utilisateur courant
                if ($success)
                    Router::redirect('public/auction/index');
            }
        }

        // view
        return View::renderTemplate('Auction/add.twig', [
            'meta' => $meta,
            'stamps' => $selectStamps,
            'errors' => $errors
        ]);
    }

    /**
     * PRIVATE ROUTE
     * route de suppression d'une enchère
     * http://localhost:8888/stampee/public/auction/delete?auction=:id
     */

    public function deleteAction()
    {
        CheckSession::SessionAuth();

        // récupérer l'id de l'enchère dans la query string
        $auctionId = $_GET["auction"];

        // requête de suppression de l'enchère
        $success = AuctionDAO::delete($auctionId);

        // on redirige vers la page d'enchères de cet utilisateur
        if ($success)
            Router::redirect('public/auction/index');
        else Router::redirect('public/gallery/index');
    }

    /**
     * PRIVATE ROUTE
     * page formulaire d'édition d'une enchère populée
     * http://localhost:8888/stampee/public/auction/select?auction=:id
     *
     * @return void
     */
    public function selectAction()
    {

        CheckSession::SessionAuth();

        $auctionId = $_GET["auction"];

        // metatags
        $meta['title'] = "Editer une enchère | Stampee";

        // get auction by id
        $auction = AuctionDAO::getAuctionById($auctionId);

        // on redirige un user qui tente d'updater une enchère qu'il n'a pas créé
        if ($auction[0]["id"] !== $_SESSION["userId"])
            Router::redirect('public/gallery/index');

        // view
        return View::renderTemplate('Auction/add.twig', [
            'meta' => $meta,
            'auction' => $auction
        ]);
    }

    /**
     * PRIVATE ROUTE
     * route de mise à jour d'une enchère
     * http://localhost:8888/stampee/public/auction/update
     */
    public function updateAction()
    {
        CheckSession::SessionAuth();

        // metatags
        $meta['title'] = "Editer une enchère | Stampee";

        // data validation
        if (isset($_REQUEST['submit'])) {
            $val = new Validation;

            $val->name('name')->value($_REQUEST['name'])->required();
            $val->name('description')->value($_REQUEST['description'])->required();
            $val->name('start_date')->value($_REQUEST['start_date'])->required();
            $val->name('end_date')->value($_REQUEST['end_date'])->required();
            $val->name('start_price')->value($_REQUEST['start_price'])->required();

            if (!$val->isSuccess()) {

                // get stamps
                $stamps = StampDAO::getStampsByAuction($_REQUEST['id']);

                $errors = $val->getErrors();

                $auction[0] = [
                    'id' => $_REQUEST['id'], 'name' => $_REQUEST['name'], 'description' => $_REQUEST['description'], 'start_date' => $_REQUEST['start_date'],
                    'end_date' => $_REQUEST['end_date'], 'start_price' => $_REQUEST['start_price']
                ];

                // view
                return View::renderTemplate('Auction/add.twig', [
                    'meta' => $meta,
                    'errors' => $errors,
                    'auction' => $auction,
                    'stamps' => $stamps
                ]);
            } else {
                $success = AuctionDAO::update($_REQUEST);

                if ($success || is_null($success))
                    Router::redirect('public/auction/index');
            }
        }
    }

    /**
     * PRIVATE ROUTE
     * route de publication d'une enchère
     * http://localhost:8888/stampee/public/auction/publish
     */
    public function publishAction()
    {
        CheckSession::SessionAuth();

        // récupérer l'id de l'enchère dans la query string
        $auctionId = $_GET["auction"];
        $publicationStatus = $_GET["publish"];

        // requête de publication de l'enchère
        $success = AuctionDAO::publish($auctionId, $publicationStatus);

        // on redirige vers la page d'enchères de cet utilisateur
        if ($success)
            Router::redirect('public/auction/index');
    }
}
