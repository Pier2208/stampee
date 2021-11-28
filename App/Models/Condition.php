<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Condition extends \Core\Model
{

    /**
     * Fetch all conditions
     * @return array
     */
    public static function getAll(): array
    {
        $db = static::getDB();
        $request = "SELECT * FROM StampCondition";
        return $db->query($request)->fetchAll(PDO::FETCH_ASSOC);
    }
}
