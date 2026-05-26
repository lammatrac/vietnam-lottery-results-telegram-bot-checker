<?php

namespace App\Services;

use DateTimeImmutable;
use InvalidArgumentException;

class LotteryService
{
    public static function fetch(
        string $province,
        string $date
    ): string {

        $formattedDate = self::normalizeDate($date);

        $url =
            "https://www.minhngoc.com.vn/getkqxs/{$province}/{$formattedDate}.js";

        return file_get_contents($url);
    }

    private static function normalizeDate(string $date): string
    {
        $parsed = DateTimeImmutable::createFromFormat('d-m-Y', $date);

        if ($parsed === false) {
            throw new InvalidArgumentException(
                'Lottery date must use dd-mm-yyyy format.'
            );
        }

        return $parsed->format('d-m-Y');
    }
}
