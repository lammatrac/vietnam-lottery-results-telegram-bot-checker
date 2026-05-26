<?php

namespace App\Services;

class LotteryParser
{
    public static function parse(
        string $js
    ): array {

        preg_match_all(
            '/<td class="(giai[a-z0-9]+)">\s*(.*?)\s*<\/td>/s',
            $js,
            $matches,
            PREG_SET_ORDER
        );

        $results = [];

        foreach ($matches as $m) {

            $name = strip_tags($m[1]);

            $value = trim(
                strip_tags(
                    html_entity_decode($m[2])
                )
            );

            $results[$name] = $value;
        }

        return $results;
    }
}
