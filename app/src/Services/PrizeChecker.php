<?php

namespace App\Services;

class PrizeChecker
{
    public static function check(
        string $ticket,
        array $results
    ): array {

        $wins = [];

        foreach ($results as $prize => $numbers) {

            $list = preg_split(
                '/\s*-\s*/',
                $numbers
            );

            foreach ($list as $number) {

                $number = trim($number);

                if (
                    str_ends_with(
                        $ticket,
                        $number
                    )
                ) {

                    $wins[] = [
                        'prize' => $prize,
                        'number' => $number
                    ];
                }
            }
        }

        return $wins;
    }
}
