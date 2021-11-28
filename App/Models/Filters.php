<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Filters extends \Core\Model
{

    /**
     * Créer les filtres à la création d'un compte utilisateur
     */
    public static function create($userId): bool
    {
        $db = static::getDB();

        $request = "INSERT INTO Filters(user_Id) VALUES(?)";
        $stmt = $db->prepare($request);
        $stmt->execute([$userId]);
        return $stmt->rowCount() > 0 ? true : false;
    }

    /**
     * Récupérer les filtres d'un utilisateur (si pas de filtre, un tab vide est retourné)
     */
    public static function get(): array
    {
        $db = static::getDB();

        $request = "SELECT 
                        f.theme_id AS theme_id,
                        f.country_id AS country_id, 
                        f.category_id AS category_id,
                        c.name AS country,
                        cat.name AS category,
                        t.name AS theme
                    FROM Filters AS f
                    LEFT JOIN Country AS c ON c.id = f.country_id
                    LEFT JOIN Category AS cat ON cat.id = f.category_id
                    LEFT JOIN Theme AS t ON t.id = f.theme_id
                    WHERE user_id = ?";

        $stmt = $db->prepare($request);
        $stmt->execute([$_SESSION["userId"]]);
        $filters = $stmt->fetch(PDO::FETCH_ASSOC);

        return array_filter($filters, function ($value) {
            return !is_null($value) && $value !== '';
        });
    }

    /**
     * Sauvegarder les filtres d'un utilisateur
     */
    public static function save($filters): bool
    {
        $db = static::getDB();

        $request = "UPDATE Filters 
                    SET theme_id = ?, 
                    country_id = ?, 
                    category_id = ?
                     WHERE user_id = ?";
        $stmt = $db->prepare($request);
        $stmt->execute([
            $filters['theme_id'] ?? NULL,
            $filters['country_id'] ?? NULL,
            $filters['category_id'] ?? NULL,
            $_SESSION["userId"]
        ]);
        return $stmt->rowCount() > 0 ? true : false;
    }

    /**
     * Réinitialiser les filtres d'un utilisateur
     */
    public static function reset(): bool
    {
        $db = static::getDB();

        $request = "UPDATE Filters SET 
                    theme_id = NULL,
                    country_id = NULL,
                    category_id = NULL
                    WHERE user_id = ?";
        $stmt = $db->prepare($request);
        $stmt->execute([$_SESSION["userId"]]);
        return $stmt->rowCount() > 0 ? true : false;
    }
}
