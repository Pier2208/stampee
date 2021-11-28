<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class FavoriteDAO extends \Core\Model
{
    /**
     * Récupérer tous les favoris d'un user
     */
    public static function getAllFavorites()
    {
        $db = static::getDB();

        // récupérer tous les auction_id appartenant à un user_id dans la table Favorite
        $request = "SELECT auction_id FROM Favorite WHERE user_id = ?";
        $stmt = $db->prepare($request);
        $stmt->execute([$_SESSION["userId"]]);

        // on retourne un tab d'une dimension [2, 4, 6] -> https://phpdelusions.net/pdo/fetch_modes#FETCH_COLUMN
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Ajouter ou supprimer une enchère des favoris
     */
    public static function toggle($auctionId): bool
    {
        $db = static::getDB();

        $request = "SELECT * FROM Favorite WHERE user_id = ? AND auction_id = ?";
        $stmt = $db->prepare($request);
        $stmt->execute([$_SESSION["userId"], $auctionId]);
        $isFavorite = $stmt->rowCount() > 0 ? true : false;

        if ($isFavorite) {
            // supprimer l'enchère des favoris
            $request = "DELETE FROM Favorite WHERE user_id = ? AND auction_id = ?";
            $stmt = $db->prepare($request);
            $stmt->execute([$_SESSION["userId"], $auctionId]);
        } else {
            // ajouterl'enchère aux favoris
            $request = "INSERT INTO Favorite(user_id, auction_id) VALUES(?, ?)";
            $stmt = $db->prepare($request);
            $stmt->execute([$_SESSION["userId"], $auctionId]);
        }

        return $stmt->rowCount() > 0 ? true : false;
    }
}
