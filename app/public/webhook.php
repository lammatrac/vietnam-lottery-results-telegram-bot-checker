<?php

ini_set('display_errors', 1);

error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../src/debug.php';

use App\Config;
use App\Services\TelegramService;
use App\Services\OpenAIService;
use App\Services\LotteryService;
use App\Services\LotteryParser;
use App\Services\PrizeChecker;
use App\Services\ProvinceMap;

ignore_user_abort(true);

set_time_limit(120);

date_default_timezone_set('Asia/Ho_Chi_Minh');

register_shutdown_function(function () {

    $error = error_get_last();

    if ($error) {

        debug_log(
            'PHP FATAL ERROR',
            $error
        );
    }
});

try {

    Config::load();

    debug_log('WEBHOOK START');

    $raw = file_get_contents('php://input');

    debug_log('RAW INPUT', $raw);

    $input = json_decode(
        $raw,
        true
    );

    debug_log('PARSED INPUT', $input);

    $message =
        $input['message']
        ?? null;

    if (!$message) {

        debug_log('NO MESSAGE');

        exit('NO MESSAGE');
    }

    $chatId =
        $message['chat']['id'];

    debug_log(
        'CHAT ID',
        $chatId
    );

    if (
        !isset($message['photo']) &&
        !isset($message['document'])
    ) {

        debug_log('NO IMAGE');

        TelegramService::reply(
            $chatId,
            'Hãy gửi ảnh vé số.'
        );

        exit;
    }

    $fileId = null;

    if (isset($message['photo'])) {

        $photos =
            $message['photo'];

        debug_log(
            'PHOTO COUNT',
            count($photos)
        );

        $largest = end($photos);

        debug_log(
            'LARGEST PHOTO',
            $largest
        );

        $fileId =
            $largest['file_id'];

        debug_log(
            'PHOTO FILE ID',
            $fileId
        );
    }

    if (isset($message['document'])) {

        debug_log(
            'DOCUMENT MODE',
            $message['document']
        );

        $fileId =
            $message['document']['file_id'];

        debug_log(
            'DOCUMENT FILE ID',
            $fileId
        );
    }

    if (!$fileId) {

        debug_log('FILE ID EMPTY');

        TelegramService::reply(
            $chatId,
            'Không lấy được file.'
        );

        exit;
    }

    debug_log(
        'BEFORE GET FILE URL'
    );

    $imageUrl =
        TelegramService::getFileUrl(
            $fileId
        );

    debug_log(
        'AFTER GET FILE URL',
        $imageUrl
    );

    if (!$imageUrl) {

        debug_log(
            'IMAGE URL FAILED'
        );

        TelegramService::reply(
            $chatId,
            'Không lấy được ảnh.'
        );

        exit;
    }

    debug_log(
        'BEFORE OPENAI OCR'
    );

    $data =
        OpenAIService::extract(
            $imageUrl
        );

    debug_log(
        'OPENAI RESULT',
        $data
    );

    $province =
        $data['province_slug']
        ?? null;

    if ($province) {

        $province = str_replace(
            '_',
            '-',
            strtolower(trim($province))
        );
    }

    $ticket =
        $data['ticket_number']
        ?? null;

    $provinceName =
        $province
            ? ProvinceMap::get($province)
            : null;

    debug_log(
        'PROVINCE',
        $province
    );

    debug_log(
        'TICKET NUMBER',
        $ticket
    );

    if (
        !$province ||
        !$ticket
    ) {

        debug_log(
            'OCR FAILED'
        );

        TelegramService::reply(
            $chatId,
            'Không đọc được vé số.'
        );

        exit;
    }

    debug_log(
        'FETCH LOTTERY RESULT'
    );

    $js =
        LotteryService::fetch(
            $province
        );

    debug_log(
        'LOTTERY JS LENGTH',
        strlen($js)
    );

    $results =
        LotteryParser::parse($js);

    debug_log(
        'PARSED RESULTS',
        $results
    );

    $wins =
        PrizeChecker::check(
            $ticket,
            $results
        );

    debug_log(
        'WIN RESULTS',
        $wins
    );

    if (!$wins) {

        $text =
            "❌ Không trúng\n\n" .
            "Vé: {$ticket}\n" .
            "Đài: {$provinceName}";

        debug_log(
            'SEND NO WIN',
            $text
        );

        TelegramService::reply(
            $chatId,
            $text
        );

        exit;
    }

    $text =
        "🎉 KẾT QUẢ DÒ VÉ\n\n";

    foreach ($wins as $win) {

        $text .=
            "- {$win['prize']}: {$win['number']}\n";
    }

    debug_log(
        'FINAL MESSAGE',
        $text
    );

    TelegramService::reply(
        $chatId,
        $text
    );

    debug_log(
        'WEBHOOK END'
    );

    if (!headers_sent()) {

        http_response_code(200);
    }

    echo 'OK';

} catch (\Throwable $e) {

    debug_log(
        'GLOBAL EXCEPTION',
        [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    );

    if (!headers_sent()) {

        http_response_code(500);
    }

    echo 'ERROR';
}
