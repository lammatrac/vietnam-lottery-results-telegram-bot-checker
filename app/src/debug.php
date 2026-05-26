<?php

function debug_log(string $title, $data = null): void
{
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

        $text .= PHP_EOL;
    }

    file_put_contents(
        '/logs/telegram.log',
        $text,
        FILE_APPEND
    );
}
