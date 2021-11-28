<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Offer extends \Core\Model
{
    /**
     * Soumettre une nouvelle offre
     */
    public static function addOffer($bet, $auctionId): bool
    {
        $db = static::getDB();

        $request = "INSERT INTO Offer(
            user_id,
            auction_id,
            current_price,
            createdAt)
            VALUES(?, ?, ?, ?)";

        $stmt = $db->prepare($request);

        $stmt->execute([
            $_SESSION["userId"],
            $auctionId,
            $bet,
            date("Y-m-d H:i:s")
        ]);

        return $stmt->rowCount() > 0 ? true : false;
    }

    /**
     * Récupérer touytes les offres faites pour une enchère
     */
    public static function getAll($auctionId): array
    {
        $db = static::getDB();

        $request = "SELECT 
                        u.username AS username, 
                        o.current_price AS current_price,
                        o.createdAt AS date
                    FROM User AS u
                    INNER JOIN Offer AS o
                        ON u.id = o.user_id
                    WHERE o.auction_id = ?
                    ORDER BY o.id DESC";

        $stmt = $db->prepare($request);
        $stmt->execute([$auctionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
