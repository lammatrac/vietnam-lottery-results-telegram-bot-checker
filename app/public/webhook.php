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
use App\Services\UpdateDeduplicator;

ignore_user_abort(true);

set_time_limit(120);

date_default_timezone_set('Asia/Ho_Chi_Minh');

register_shutdown_function(function () {

    $error = error_get_last();

    if ($error) {

        debug_log(
            'PHP FATAL ERROR',
            $error,
            'error'
        );
    }
});

try {

    Config::load();

    $acknowledged = false;

    $ack = function () use (&$acknowledged): void {

        if ($acknowledged) {

            return;
        }

        if (!headers_sent()) {

            http_response_code(200);
            header('Content-Type: text/plain; charset=utf-8');
        }

        echo 'OK';

        if (function_exists('fastcgi_finish_request')) {

            fastcgi_finish_request();

        } else {

            @ob_flush();
            flush();
        }

        $acknowledged = true;
    };

    debug_log('WEBHOOK START', null, 'info');

    $raw = file_get_contents('php://input');

    $input = json_decode(
        $raw,
        true
    );

    if (!is_array($input)) {

        $ack();

        debug_log(
            'INVALID JSON INPUT',
            $raw,
            'error'
        );

        exit;
    }

    $updateId =
        $input['update_id']
        ?? null;

    if ($updateId !== null && UpdateDeduplicator::isDuplicate($updateId)) {

        $ack();

        debug_log(
            'SKIP DUPLICATE UPDATE',
            $updateId,
            'info'
        );

        exit;
    }

    $message =
        $input['message']
        ?? null;

    if (!$message) {

        $ack();

        debug_log('NO MESSAGE', null, 'info');

        exit;
    }

    $chatId =
        $message['chat']['id'];

    if (
        !isset($message['photo']) &&
        !isset($message['document'])
    ) {

        $ack();

        debug_log('NO IMAGE', null, 'info');

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

        $largest = end($photos);

        $fileId =
            $largest['file_id'];

    }

    if (isset($message['document'])) {

        $fileId =
            $message['document']['file_id'];
    }

    if (!$fileId) {

        $ack();

        debug_log('FILE ID EMPTY', null, 'error');

        TelegramService::reply(
            $chatId,
            'Không lấy được file.'
        );

        exit;
    }

    $ack();

    $imageUrl =
        TelegramService::getFileUrl(
            $fileId
        );

    if (!$imageUrl) {

        debug_log(
            'IMAGE URL FAILED',
            null,
            'error'
        );

        TelegramService::reply(
            $chatId,
            'Không lấy được ảnh.'
        );

        exit;
    }

    $data =
        OpenAIService::extract(
            $imageUrl
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

    $resultDate =
        $data['result_date']
        ?? null;

    $provinceName =
        $province
            ? ProvinceMap::get($province)
            : null;

    $region =
        $province
            ? ProvinceMap::getRegion($province)
            : null;

    $checkUrl =
        ($province && $region && $resultDate)
            ? "https://www.minhngoc.net.vn/ket-qua-xo-so/{$region}/{$province}/{$resultDate}.html"
            : null;

    if (
        !$province ||
        !$ticket ||
        !$resultDate
    ) {

        debug_log(
            'OCR FAILED',
            $data,
            'error'
        );

        TelegramService::reply(
            $chatId,
            'Không đọc được vé số.'
        );

        exit;
    }

    $js =
        LotteryService::fetch(
            $province,
            $resultDate
        );

    $results =
        LotteryParser::parse($js);

    $wins =
        PrizeChecker::check(
            $ticket,
            $results
        );

    if (!$wins) {

        $text =
            "❌ Không trúng\n\n" .
            "Vé: {$ticket}\n" .
            "Đài: {$provinceName}\n" .
            "Ngày: {$resultDate}";

        if ($checkUrl) {

            $text .= "\n\n🔗 Kiểm tra thủ công:\n{$checkUrl}";
        }

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

    $text .=
        "\nVé: {$ticket}\n" .
        "Đài: {$provinceName}\n" .
        "Ngày: {$resultDate}";

    if ($checkUrl) {

        $text .= "\n\n🔗 Kiểm tra thủ công:\n{$checkUrl}";
    }

    TelegramService::reply(
        $chatId,
        $text
    );

    debug_log('WEBHOOK END', null, 'info');

} catch (\Throwable $e) {

    debug_log(
        'GLOBAL EXCEPTION',
        [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ],
        'error'
    );
}
