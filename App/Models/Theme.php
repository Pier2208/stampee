<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Theme extends \Core\Model
{

    /**
     * Fetch all themes
     * @return array
     */
    public static function getAll(): array
    {
        $db = static::getDB();
        $request = "SELECT * FROM Theme ORDER BY name ASC";
        return $db->query($request)->fetchAll(PDO::FETCH_ASSOC);
    }
}
