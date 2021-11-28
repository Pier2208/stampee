<?php

namespace App\Controllers;

use \Core\View;
use \Core\Router;
use \Core\CheckSession;
use App\Models\FavoriteDAO;
use App\Models\AuctionDAO;
use App\Models\Offer;
use \App\Functions\Utils;

class Favorite extends \Core\Controller
{

    /**
     * PRIVATE ROUTE
     * page des favoris d'un utilisateur
     * http://localhost:8888/stampee/public/favorite/index
     *
     * @return void
     */
    public function indexAction()
    {
        CheckSession::SessionAuth();

        // metatags
        $meta['title'] = "Mes favoris | Stampee";

        // récupérer tous les favoris
        $auctionIds = FavoriteDAO::getAllFavorites();

        // pour chaque auction_id, retourner l'enchère
        $auctions = [];
        foreach ($auctionIds as $auctionId) {
            $auction = AuctionDAO::getAuctionById($auctionId);
            array_push($auctions, $auction);
        }

        // calculer le status de l'enchère
        foreach ($auctions as &$auction) {

            $auctionStatus = Utils::auctionStatus($auction[0]["start_date"], $auction[0]["end_date"]);
            $auction["status"] = $auctionStatus["msg"];
            $auction["statusKey"] = $auctionStatus["value"];
            $auction["isFavorite"] = AuctionDAO::isFavorite($auction[0]["id"]);

            // récupérer le montant de la dernière offre si elle existe
            $offers = Offer::getAll($auction[0]["id"]);
            if (count($offers) > 0) {
                $auction["current_price"] = $offers[0]["current_price"];
            }
        }

        // view
        return View::renderTemplate('Favorite/index.twig', [
            'meta' => $meta,
            'auctions' => $auctions
        ]);
    }

    /**
     * PRIVATE ROUTE
     * ajouter une enchère aux favoris
     * http://localhost:8888/stampee/public/favorite/toggle?auction=:id
     **/
    public function toggleAction()
    {
        CheckSession::SessionAuth();

        // récupérer l'id de l'enchère dans la query string
        $auctionId = $_GET["auction"];

        if ($auctionId) {
            $success = FavoriteDAO::toggle($auctionId);

            if ($success)
                Router::redirect(explode('/stampee/', $_SERVER['HTTP_REFERER'])[1]);
        }
    }
}
