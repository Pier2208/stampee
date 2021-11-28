<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Category extends \Core\Model
{

    /**
     * Fetch all categories
     * @return array
     */
    public static function getAll(): array
    {
        $db = static::getDB();
        $request = "SELECT * FROM Category ORDER BY name ASC";
        return $db->query($request)->fetchAll(PDO::FETCH_ASSOC);
    }
}
