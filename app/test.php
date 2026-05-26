<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\LotteryService;
use App\Services\LotteryParser;

// ─── Helpers ─────────────────────────────────────────────────────────────────

function pass(string $msg): void
{
    echo "\033[32m✔ PASS\033[0m  {$msg}\n";
}

function fail(string $msg, string $detail = ''): void
{
    echo "\033[31m✘ FAIL\033[0m  {$msg}";
    if ($detail !== '') {
        echo "  ({$detail})";
    }
    echo "\n";
}

function section(string $title): void
{
    echo "\n\033[1;34m── {$title} ──\033[0m\n";
}

function dump_parsed(array $result): void
{
    if (empty($result)) {
        echo "  \033[2m(empty)\033[0m\n";
        return;
    }
    foreach ($result as $key => $value) {
        printf("  \033[36m%-20s\033[0m %s\n", $key, $value);
    }
}

// ─── LotteryParser unit tests (no network) ───────────────────────────────────

section('LotteryParser::parse()');

// Test 1: parses a single prize correctly
$html = '<td class="giaidacbiet">123456</td>';
$result = LotteryParser::parse($html);
if (isset($result['giaidacbiet']) && $result['giaidacbiet'] === '123456') {
    pass('Parses giaidacbiet correctly');
} else {
    fail('Parses giaidacbiet correctly', var_export($result, true));
}
dump_parsed($result);

// Test 2: strips inner HTML tags from prize value
$html = '<td class="giai1"><span>78</span><span>901</span></td>';
$result = LotteryParser::parse($html);
if (isset($result['giai1']) && $result['giai1'] === '78901') {
    pass('Strips inner HTML tags from prize value');
} else {
    fail('Strips inner HTML tags from prize value', var_export($result, true));
}
dump_parsed($result);

// Test 3: handles multiple prizes in one block
$html  = '<td class="giaidacbiet">654321</td>';
$html .= '<td class="giai1">11111</td>';
$html .= '<td class="giai2">22222</td>';
$result = LotteryParser::parse($html);
if (count($result) === 3 && $result['giaidacbiet'] === '654321' && $result['giai2'] === '22222') {
    pass('Handles multiple prize rows');
} else {
    fail('Handles multiple prize rows', var_export($result, true));
}
dump_parsed($result);

// Test 4: returns empty array when no matching td elements
$result = LotteryParser::parse('<table><tr><td class="other">data</td></tr></table>');
if ($result === []) {
    pass('Returns empty array when no giai* td found');
} else {
    fail('Returns empty array when no giai* td found', var_export($result, true));
}
dump_parsed($result);

// Test 5: decodes HTML entities in prize value
$html = '<td class="giai3">1&amp;2</td>';
$result = LotteryParser::parse($html);
if (isset($result['giai3']) && $result['giai3'] === '1&2') {
    pass('Decodes HTML entities in prize value');
} else {
    fail('Decodes HTML entities in prize value', var_export($result, true));
}
dump_parsed($result);

// Test 6: trims whitespace from prize value
$html = '<td class="giai4">  99999  </td>';
$result = LotteryParser::parse($html);
if (isset($result['giai4']) && $result['giai4'] === '99999') {
    pass('Trims whitespace from prize value');
} else {
    fail('Trims whitespace from prize value', var_export($result, true));
}
dump_parsed($result);

// ─── LotteryService integration test (live network) ──────────────────────────

section('LotteryService::fetch()');

$province = 'tp-hcm';

echo "  Fetching province: {$province} ...\n";

$raw = LotteryService::fetch($province);

// Test 7: returns a non-empty string
if (is_string($raw) && strlen($raw) > 0) {
    pass('Returns non-empty string for ' . $province);
} else {
    fail('Returns non-empty string for ' . $province);
}

// Test 8: content looks like JS/HTML (contains 'giai')
if (str_contains($raw, 'giai')) {
    pass("Response contains expected 'giai' prize markers");
} else {
    fail("Response contains expected 'giai' prize markers", 'first 200 chars: ' . substr($raw, 0, 200));
}

// ─── End-to-end: fetch + parse ────────────────────────────────────────────────

section('End-to-end: LotteryService + LotteryParser');

$parsed = LotteryParser::parse($raw);

echo "\n  \033[1mLotteryParser output:\033[0m\n";
dump_parsed($parsed);
echo "\n";

// Test 9: parsed result is a non-empty array
if (is_array($parsed) && count($parsed) > 0) {
    pass('Parsed result is a non-empty array (' . count($parsed) . ' prizes)');
} else {
    fail('Parsed result is a non-empty array', var_export($parsed, true));
}

// Test 10: giaidacbiet key exists
if (array_key_exists('giaidacbiet', $parsed)) {
    pass("Key 'giaidacbiet' present in parsed result: " . $parsed['giaidacbiet']);
} else {
    fail("Key 'giaidacbiet' missing from parsed result", implode(', ', array_keys($parsed)));
}

// Test 11: prize values are non-empty strings
$allNonEmpty = true;
foreach ($parsed as $key => $value) {
    if (!is_string($value) || trim($value) === '') {
        $allNonEmpty = false;
        fail("Empty or non-string value for key: {$key}");
    }
}
if ($allNonEmpty) {
    pass('All parsed prize values are non-empty strings');
}

echo "\n\033[1mDone.\033[0m\n\n";
