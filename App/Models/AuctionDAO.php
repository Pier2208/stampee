<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use \App\Functions\Utils;

class AuctionDAO extends \Core\Model
{

    /**
     * Récupérer toutes les enchères
     */
    public static function getAllAuctions($filters)
    {
        $db = static::getDB();

        $getAuctions = "SELECT 
                            id AS auctionId, 
                            name AS auctionName,
                            start_date, 
                            end_date, 
                            start_price
                        FROM Auction
                        WHERE published = ?";

        $stmt = $db->prepare($getAuctions);
        $stmt->execute(['1']);
        $auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $getStamps = "SELECT 
                        s.id AS stampId,
                        i.url AS image,
                        s.theme_id as theme_id,
                        s.category_id AS category_id,
                        s.country_id AS country_id
                        FROM Stamp AS s
                        INNER JOIN Image as i
                        ON s.id = i.stamp_id
                        WHERE s.auction_id = ?";

        $stmt = $db->prepare($getStamps);

        $results = [];
        foreach ($auctions as $auction) {
            $stmt->execute([$auction['auctionId']]);
            $stamps = $stmt->fetch(PDO::FETCH_ASSOC);
            array_push($results, array_merge($auction, $stamps));
        }

        // calculer le status de l'enchère
        foreach ($results as &$auction) {
            $auctionStatus = Utils::auctionStatus($auction["start_date"], $auction["end_date"]);
            $auction["status"] = $auctionStatus["msg"];
            $auction["statusKey"] = $auctionStatus["value"];
            $auction["isFavorite"] = AuctionDAO::isFavorite($auction["auctionId"]);

            // récupérer le montant de la dernière offre si elle existe
            $offers = Offer::getAll($auction["auctionId"]);
            if (count($offers) > 0) {
                $auction["current_price"] = $offers[0]["current_price"];
            }
        }

        // si on a des filtres actifs
        if (count($filters) > 0) {
            $filteredResults = [];
            
            foreach ($results as &$auction) {
                $flag = true;
                foreach ($filters as $key => $value) {

                    if (isset($auction[$key]) && !in_array($auction[$key], explode(',', $value))) {
                        $flag = false;
                        break;
                    }
          
                }
                if ($flag) $filteredResults[] = $auction;
            }
            return $filteredResults;
        } else {
            return $results;
        }
    }

    /**
     * Récupérer toutes les enchères d'un utilisateur
     */
    public static function getAllAuctionsByUser($userId): array
    {
        $db = static::getDB();

        $getAuctions = "SELECT 
                            id AS auctionId, 
                            name AS auctionName, 
                            description AS auctionDescription, 
                            start_date, 
                            end_date, 
                            start_price,
                            published
                        FROM Auction
                        WHERE user_id = ?";

        $stmt = $db->prepare($getAuctions);
        $stmt->execute([$userId]);
        $auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $getStamps = "SELECT 
                            s.id AS stampId,
                            s.name AS stampName,
                            s.year AS stampYear,
                            s.width AS stampWidth, 
                            s.height AS stampHeight,
                            i.url AS image,
                            i.public_id AS public_id
                        FROM Stamp AS s
                        INNER JOIN Image as i
                        ON s.id = i.stamp_id
                        WHERE s.auction_id = ?";

        $stmt = $db->prepare($getStamps);

        $result = [];
        foreach ($auctions as $auction) {
            $stmt->execute([$auction['auctionId']]);
            $stamps = $stmt->fetchAll(PDO::FETCH_ASSOC);
            array_push($result, array_merge($auction, $stamps));
        }
        return $result;
    }

    /**
     * Ajouter une nouvelle enchère
     */
    public static function create($auction)
    {
        $db = static::getDB();

        try {

            $db->beginTransaction();

            $auctionRequest = "INSERT INTO Auction(
                name,
                description,
                start_date,
                end_date,
                start_price,
                createdAt,
                user_id) 
               VALUES(?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($auctionRequest);

            $stmt->execute([
                $auction['name'],
                $auction['description'],
                $auction['start_date'],
                $auction['end_date'],
                $auction['start_price'],
                date("Y-m-d H:i:s"),
                $_SESSION["userId"]
            ]);

            // get the auctionId
            $auctionId = $db->lastInsertId();

            // Insert auctionId into Stamps
            $stmt = $db->prepare("UPDATE Stamp SET Stamp.auction_id = ? WHERE Stamp.id = ?");
            foreach ($auction['stamps'] as $stampId) {
                $stmt->execute([$auctionId, $stampId]);
            }
            return $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            echo $e->getMessage();
        }
    }

    /**
     * Supprimer une enchère
     */
    public static function delete($auctionId): bool
    {
        $db = static::getDB();

        // supprimer le timbre
        $request = "DELETE FROM Auction WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($request);
        $stmt->execute([$auctionId, $_SESSION["userId"]]);
        return $stmt->rowCount() > 0 ? true : false;
    }

    /**
     * Récupérer une enchère par son Id
     */
    public static function getAuctionById($auctionId): array
    {
        $db = static::getDB();

        // récupérer l'enchère par id
        $auctionRequest = "SELECT 
                        a.id, 
                        a.name AS name,
                        a.description AS description,
                        a.start_date AS start_date,
                        a.end_date AS end_date,
                        a.start_price AS start_price,
                        p.image AS userImage,
                        u.username AS username
                    FROM Auction AS a
                    INNER JOIN Profile AS p
                        ON a.user_id = p.user_id
                    INNER JOIN User AS u
                        ON a.user_id = u.id
                    WHERE a.id = ?";

        $stmt = $db->prepare($auctionRequest);
        $stmt->execute([$auctionId]);
        $auction = $stmt->fetch(PDO::FETCH_ASSOC);

        // récupérer les images associées à cette enchère
        $stampsRequest = "SELECT 
                            s.id AS stampId,
                            s.name AS stampName,
                            s.description AS stampDescription,
                            s.year AS stampYear,
                            s.width AS stampWidth,
                            s.height AS stampHeight,
                            coun.name AS stampCountry,
                            cat.name AS stampCategory,
                            sc.name AS stampCondition,
                            t.name AS stampTheme,
                            i.url AS url
                        FROM Stamp AS s
                        INNER JOIN Image as i
                            ON s.id = i.stamp_id
                        INNER JOIN Country AS coun
                            ON s.country_id = coun.id
                        INNER JOIN Category AS cat
                            ON s.category_id = cat.id
                        INNER JOIN StampCondition AS sc
                            ON s.condition_id = sc.id
                        INNER JOIN Theme AS t
                            ON s.theme_id = t.id
                        WHERE s.auction_id = ?";

        $stmt = $db->prepare($stampsRequest);
        $stmt->execute([$auctionId]);
        $stamps = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // merger l'enchère avec ses images
        $result = [];
        array_push($result, $auction, $stamps);
        return $result;
    }

    /**
     * Mettre un jour une enchère
     */
    public static function update($auctionUpdate): ?bool
    {
        $db = static::getDB();

        $updateSQL = "UPDATE Auction SET
                            name = ?,
                            description = ?,
                            start_date = ?,
                            end_date = ?,
                            start_price = ?
                            WHERE id = ? and user_id = ?";

        $stmt = $db->prepare($updateSQL);
        $stmt->execute([
            $auctionUpdate['name'],
            $auctionUpdate['description'],
            $auctionUpdate['start_date'],
            $auctionUpdate['end_date'],
            $auctionUpdate['start_price'],
            $auctionUpdate['id'],
            $_SESSION["userid"]
        ]);
        // si user clic sur submit sans rien changer au formulaire, on retourne null
        return $stmt->rowCount() > 0 ? true : null;
    }

    /**
     * Publier une enchère
     */
    public static function publish($auctionId, $publicationStatus): bool
    {
        $db = static::getDB();

        $request = "UPDATE Auction SET published = ? WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($request);
        $stmt->execute([$publicationStatus, $auctionId, $_SESSION["userId"]]);
        return $stmt->rowCount() > 0 ? true : false;
    }


    public static function isFavorite($auctionId): bool
    {
        $db = static::getDB();

        $request = "SELECT auction_id FROM Favorite WHERE user_id = ? AND auction_id = ?";

        $stmt = $db->prepare($request);
        $stmt->execute([$_SESSION["userId"], $auctionId]);
        return $stmt->fetch(PDO::FETCH_COLUMN) ? true : false;
    }
}
