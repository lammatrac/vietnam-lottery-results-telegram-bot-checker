<?php

namespace App\Services;

use App\Config;

class TelegramService
{
    public static function api(
        string $method,
        array $data = []
    ) {

        $token =
            Config::get(
                'TELEGRAM_BOT_TOKEN'
            );

        $url =
            "https://api.telegram.org/bot{$token}/{$method}";

        $ch = curl_init($url);

        curl_setopt_array($ch, [

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_POST => true,

            CURLOPT_POSTFIELDS => $data,

            CURLOPT_TIMEOUT => 30,

            CURLOPT_SSL_VERIFYPEER => false,

            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);

        $error =
            curl_error($ch);

        $httpCode =
            curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );

        curl_close($ch);

        $parsed = json_decode(
            $response,
            true
        );

        if ($error !== '' || $httpCode >= 400 || !($parsed['ok'] ?? false)) {

            debug_log(
                'TELEGRAM API URL',
                $url,
                'error'
            );

            debug_log(
                'TELEGRAM API DATA',
                $data,
                'error'
            );

            debug_log(
                'TELEGRAM API HTTP CODE',
                $httpCode,
                'error'
            );

            debug_log(
                'TELEGRAM API CURL ERROR',
                $error,
                'error'
            );

            debug_log(
                'TELEGRAM API RESPONSE',
                $response,
                'error'
            );
        }

        return $parsed;
    }

    public static function reply(
        int|string $chatId,
        string $text
    ): void {

        self::api(
            'sendMessage',
            [
                'chat_id' => $chatId,
                'text' => $text,
                'disable_web_page_preview' => true
            ]
        );
    }

    public static function getFileUrl(
        string $fileId
    ): ?string {

        $result = self::api(
            'getFile',
            [
                'file_id' => $fileId
            ]
        );

        if (
            !($result['ok'] ?? false)
        ) {

            return null;
        }

        $path =
            $result['result']['file_path'];

        $token =
            Config::get(
                'TELEGRAM_BOT_TOKEN'
            );

        $url =
            "https://api.telegram.org/file/bot{$token}/{$path}";

        return $url;
    }
}
