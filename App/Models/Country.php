<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Country extends \Core\Model
{

    /**
     * Fetch all countries
     * @return array $countries
     */
    public static function getAll(): array
    {
        $db = static::getDB();
        $request = "SELECT id, name, iso FROM Country ORDER BY name ASC";
        return $db->query($request)->fetchAll(PDO::FETCH_ASSOC);
    }
}
