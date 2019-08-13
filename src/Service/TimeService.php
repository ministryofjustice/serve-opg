<?php declare(strict_types=1);

namespace App\Service;

use DateTime;

class TimeService
{
    public static function currentDay(): DateTime
    {
        return DateTime::createFromFormat('U', (string) time())->setTime(0, 0);
    }

    public static function currentDateTime(): DateTime
    {
        return DateTime::createFromFormat('U', (string) time());
    }

    public static function newDateTimeFromDate(string $date): DateTime
    {
        $dateTime = (new DateTime($date))->format('U');
        return DateTime::createFromFormat('U', $dateTime);
    }
}
