<?php

namespace App\Services;

class LotteryService
{
    public static function fetch(
        string $province
    ): string {

        $url =
            "https://www.minhngoc.com.vn/getkqxs/{$province}.js";

        return file_get_contents($url);
    }
}
