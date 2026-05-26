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

        debug_log('TELEGRAM API URL', $url);

        debug_log('TELEGRAM API DATA', $data);

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

        debug_log(
            'TELEGRAM API HTTP CODE',
            $httpCode
        );

        debug_log(
            'TELEGRAM API CURL ERROR',
            $error
        );

        debug_log(
            'TELEGRAM API RESPONSE',
            $response
        );

        return json_decode(
            $response,
            true
        );
    }

    public static function reply(
        int|string $chatId,
        string $text
    ): void {

        debug_log(
            'SEND MESSAGE',
            $text
        );

        self::api(
            'sendMessage',
            [
                'chat_id' => $chatId,
                'text' => $text
            ]
        );
    }

    public static function getFileUrl(
        string $fileId
    ): ?string {

        debug_log(
            'GET FILE URL',
            $fileId
        );

        $result = self::api(
            'getFile',
            [
                'file_id' => $fileId
            ]
        );

        debug_log(
            'GET FILE RESULT',
            $result
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

        debug_log(
            'FINAL FILE URL',
            $url
        );

        return $url;
    }
}
