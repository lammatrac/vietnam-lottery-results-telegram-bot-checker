<?php

function debug_log(
    string $title,
    $data = null,
    string $level = 'debug'
): void
{
    $weights = [
        'none' => 0,
        'error' => 1,
        'info' => 2,
        'debug' => 3,
    ];

    $currentLevel = strtolower(
        (string) (\App\Config::get('LOG_LEVEL', 'error'))
    );

    $currentWeight =
        $weights[$currentLevel]
        ?? $weights['error'];

    $messageWeight =
        $weights[strtolower($level)]
        ?? $weights['debug'];

    if ($messageWeight > $currentWeight) {

        return;
    }

    $text = PHP_EOL .
        str_repeat('=', 100) .
        PHP_EOL;

    $text .= '[' . date('Y-m-d H:i:s') . '] ';
    $text .= $title;
    $text .= PHP_EOL;

    if ($data !== null) {

        if (
            is_array($data) ||
            is_object($data)
        ) {

            $text .= json_encode(
                $data,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            );

        } else {

            $text .= (string) $data;
        }

        if (strlen($text) > 12000) {

            $text = substr($text, 0, 12000) .
                PHP_EOL .
                '[truncated]';
        }

        $text .= PHP_EOL;
    }

    file_put_contents(
        '/logs/telegram.log',
        $text,
        FILE_APPEND
    );
}
