<?php

namespace App\Services;

class LotteryParser
{
    public static function parse(
        string $js
    ): array {

        preg_match_all(
            '/<td\b[^>]*class="(giai[a-z0-9]+)"[^>]*>\s*(.*?)\s*<\/td>/si',
            $js,
            $matches,
            PREG_SET_ORDER
        );

        $labels = [];

        foreach ($matches as $m) {

            $class = strtolower(trim($m[1]));

            if (!str_ends_with($class, 'l')) {

                continue;
            }

            $baseClass = substr($class, 0, -1);

            $label = trim(
                strip_tags(
                    html_entity_decode($m[2])
                )
            );

            if ($baseClass !== '' && $label !== '') {

                $labels[$baseClass] = $label;
            }
        }

        $results = [];

        foreach ($matches as $m) {

            $class = strtolower(trim($m[1]));

            if (str_ends_with($class, 'l')) {

                continue;
            }

            $value = trim(
                strip_tags(
                    html_entity_decode($m[2])
                )
            );

            if ($value === '') {

                continue;
            }

            $name =
                $labels[$class]
                ?? $class;

            $results[$name] = $value;
        }

        return $results;
    }
}
