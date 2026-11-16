<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeImmutable;
use DateTimeZone;

final class ProductDate
{
    public static function today(): DateTimeImmutable
    {
        return new DateTimeImmutable('today', new DateTimeZone('Europe/Bucharest'));
    }
}
