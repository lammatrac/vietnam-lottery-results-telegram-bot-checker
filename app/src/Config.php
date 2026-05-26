<?php

namespace App;

class Config
{
    private static array $env = [];

    public static function load(): void
    {
        self::$env = parse_ini_file(
            __DIR__ . '/../.env'
        );
    }

    public static function get(
        string $key,
        ?string $default = null
    ): ?string
    {
        return self::$env[$key] ?? $default;
    }
}
