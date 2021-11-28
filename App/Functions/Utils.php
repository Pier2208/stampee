<?php

declare(strict_types=1);

namespace App\Functions;


class Utils
{
    public static function auctionStatus($auctionStart, $auctionEnd): array
    {
        $now = new \DateTime();
        $start = new \DateTime($auctionStart);
        $end = new \DateTime($auctionEnd);

        $status = [];

        if ($now->getTimestamp() < $start->getTimeStamp()) {
            $interval = date_diff($start, $now);
            $day = $interval->d > 0 ? $interval->d . ' jours' : '';
            $hour = $interval->h > 0 ? $interval->h . 'h' : '';
            $minute = $interval->i > 0 ? $interval->i . 'mn' : '';
            $status = ["msg" => "L'enchère débute dans {$day} {$hour} {$minute}", "value" => "forthcoming"];
        } elseif ($now->getTimeStamp() > $start->getTimeStamp() && $now->getTimeStamp() < $end->getTimeStamp()) {
            $interval = date_diff($now, $end);
            $day = $interval->d > 0 ? $interval->d . 'jours' : '';
            $hour = $interval->h > 0 ? $interval->h . 'h' : '';
            $minute = $interval->i > 0 ? $interval->i . 'mn' : '';
            $status = "L'enchère s'achève dans {$day} {$hour} {$minute}";
            $status = ["msg" => "L'enchère s'achève dans {$day} {$hour} {$minute}", "value" => "ongoing"];
        } else {
            $status = ["msg" => "Cette enchère est expirée", "value" => "expired"];
        }

        return $status;
    }
}
