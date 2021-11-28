<?php

namespace App\Controllers;

use \Core\View;
use \Core\CheckSession;
use \Core\Validation;
use \App\Models\AuctionDAO;
use \App\Models\Offer;
use \App\Functions\Utils;
use \Core\Router;
use \App\Models\Country;
use \App\Models\Category;
use \App\Models\Theme;
use \App\Models\Filters;

class Gallery extends \Core\Controller
{

    /**
     * Show the index gallery page
     * http://localhost:8888/stampee/public/gqllery/index
     *
     * @return void
     */
    public function indexAction()
    {

        CheckSession::SessionAuth();

        // metatags
        $meta['title'] = "Rechercher des enchères | Stampee";
        $meta['description'] = "Rechercher parmi des centaines d'enchères.";

        //récupérer tous les filtres
        $filters = Filters::get($_SESSION["userId"]);

        // récupérer toutes les enchères
        $allAuctions = AuctionDAO::getAllAuctions($filters);
        $auctionsCount = count($allAuctions);

        // populer select country
        $selectCountries = Country::getAll();

        // populer select category
        $selectCategories = Category::getAll();

        // populer select theme
        $selectThemes = Theme::getAll();

        if (isset($_REQUEST["save"])) {
            $userFilters["theme_id"] = isset($_REQUEST["by_theme"]) && count($_REQUEST["by_theme"]) > 0 ? implode(',', $_REQUEST["by_theme"]) : NULL;
            $userFilters["category_id"] = isset($_REQUEST["by_category"]) && count($_REQUEST["by_category"]) > 0 ? implode(',', $_REQUEST["by_category"]) : NULL;
            $userFilters["country_id"] = isset($_REQUEST["by_country"]) && count($_REQUEST["by_country"]) > 0 ? implode(',', $_REQUEST["by_country"]) : NULL;

            $success = Filters::save($userFilters);
            if ($success)
                Router::redirect('public/gallery/index');
        } elseif (isset($_REQUEST["reset"])) {
            $success = Filters::reset();
            if ($success)
                Router::redirect('public/gallery/index');
        }

        // view
        return View::renderTemplate('Gallery/index.twig', [
            'meta' => $meta,
            'auctions' => $allAuctions,
            'auctionsCount' => $auctionsCount,
            'countries' => $selectCountries,
            'themes' => $selectThemes,
            'categories' => $selectCategories,
            "filters" => array_filter($filters, function ($key) {
                return strpos($key, 'id') === false;
            }, ARRAY_FILTER_USE_KEY)
        ]);
    }

    /**
     * Affiche la page d'une enchère
     * http://localhost:8888/stampee/public/auction/select?auction=:id
     *
     * @return void
     */
    public function selectAction()
    {

        CheckSession::SessionAuth();

        // get auction id in the query string
        $auctionId = $_GET["auction"];

        // récupérer l'enchère par id
        $auction = AuctionDAO::getAuctionById($auctionId);

        if (!$auction[0]) {
            Router::redirect('public/gallery/index');
            die();
        }

        $auctionStatus = Utils::auctionStatus($auction[0]["start_date"], $auction[0]["end_date"]);
        $auction["status"] = $auctionStatus["msg"];
        $auction["statusKey"] = $auctionStatus["value"];

        // récupérer les offres
        $offers = Offer::getAll($auctionId);

        // metatags
        $meta["title"] = $auction[0]["name"] . "| Stampee";
        $meta["description"] = $auction[0]["description"];

        // errors
        $errors = [];

        if (isset($_REQUEST['submit'])) {
            $val = new Validation;

            // $val->name('bet')->value($_REQUEST['bet'])->min($auction[0]["start_price"] + $auction[0]["start_price"] * 10 / 100)->required();

            if (!$val->isSuccess()) {
                $errors = $val->getErrors();
            } else {
                $success = Offer::addOffer($_REQUEST['bet'], $auctionId);

                if ($success)
                    Router::redirect('public/gallery/select?auction=' . $auctionId);
            }
        }


        // view
        return View::renderTemplate('Gallery/select.twig', [
            "meta" => $meta,
            "auction" => $auction,
            "errors" => $errors,
            'offers' => $offers
        ]);
    }
}
