# Vietnam Lottery Results Telegram Bot Checker

Telegram bot backend that reads Vietnam lottery ticket images, extracts ticket info with OpenAI Vision, fetches official results by province, and checks if the ticket wins.

## What This Project Does

- Receives a ticket image from Telegram.
- Gets a public Telegram file URL for that image.
- Sends the image to OpenAI to extract:
	- province_slug
	- ticket_number
- Fetches province result data from Minh Ngoc.
- Parses prize numbers from the HTML/JS response.
- Compares ticket suffix with prize numbers.
- Replies to Telegram with win or no-win result.

## Tech Stack

- PHP 8.3 (FPM container)
- Docker Compose
- cURL for HTTP calls
- OpenAI Chat Completions API (Vision input)
- Telegram Bot API

## Repository Structure

```text
.
├── docker-compose.yml
├── README.md
├── logs/
└── app/
		├── Dockerfile
		├── composer.json
		├── composer.lock
		├── public/
		│   ├── index.php
		│   └── webhook.php
		├── src/
		│   ├── Config.php
		│   ├── debug.php
		│   └── Services/
		│       ├── LotteryParser.php
		│       ├── LotteryService.php
		│       ├── OpenAIService.php
		│       ├── PrizeChecker.php
		│       ├── ProvinceMap.php
		│       └── TelegramService.php
		├── test.php
		└── vendor/
```

## Runtime Flow

Main entrypoint: app/public/webhook.php

1. Load environment values from app/.env via Config::load().
2. Read Telegram webhook payload from php://input.
3. Validate incoming message and ensure image exists (photo or document).
4. Get file_id from Telegram message.
5. Resolve image URL with TelegramService::getFileUrl().
6. Extract province_slug and ticket_number with OpenAIService::extract().
7. Fetch province result JS from Minh Ngoc with LotteryService::fetch().
8. Parse all giai* entries with LotteryParser::parse().
9. Check wins by suffix match with PrizeChecker::check().
10. Send final response back through TelegramService::reply().

## Service Responsibilities

- Config.php
	- Loads key-value config from app/.env.
- debug.php
	- Appends structured logs to /logs/telegram.log.
- TelegramService.php
	- Telegram API wrapper:
		- getFile
		- sendMessage
		- file URL generation
- OpenAIService.php
	- Sends image URL to OpenAI and returns parsed JSON object.
- LotteryService.php
	- Downloads province draw data from:
		- https://www.minhngoc.com.vn/getkqxs/{province}.js
- LotteryParser.php
	- Regex parser for td elements with class names matching giai*.
- PrizeChecker.php
	- Splits prize numbers and checks ticket suffix matches.
- ProvinceMap.php
	- Maps province slug to display name and provides supported slugs.

## Required Environment Variables

Create file: app/.env

```ini
TELEGRAM_BOT_TOKEN=your_telegram_bot_token
OPENAI_API_KEY=your_openai_api_key
```

## Local Run with Docker

From repository root:

```bash
docker compose up -d --build
```

Service mapping in docker-compose.yml:

- Container name: kqxs_php
- Port: 9000:9000
- Workdir: /var/www/html
- Volume mounts:
	- ./app -> /var/www/html
	- ./logs -> /logs

Health check endpoint (basic):

```bash
curl -I http://localhost:9000
```

## Configure Telegram Webhook

Set webhook URL to your public endpoint that points to app/public/webhook.php.

Example:

```bash
curl -X POST "https://api.telegram.org/bot<TELEGRAM_BOT_TOKEN>/setWebhook" \
	-d "url=https://your-domain.com/webhook.php"
```

## Testing

Test script: app/test.php

It includes:

- Unit tests for LotteryParser::parse() (no network).
- Integration tests for LotteryService::fetch() (network required).
- End-to-end parse checks after fetch.
- Parsed output display for each parser test.

Run:

```bash
cd app
php test.php
```

Note:

- In restricted/sandbox terminals, external DNS and HTTP may fail.
- In that case parser-only tests pass, while live fetch tests fail.

## Logs and Debugging

Log file:

- logs/telegram.log (mounted into container as /logs/telegram.log)

The app logs:

- Incoming webhook payload
- Telegram API requests/responses
- OpenAI extraction output
- Parsed lottery results
- Win-check results
- Fatal errors via shutdown handler

## Input and Output Behavior

Incoming Telegram message types supported:

- photo
- document

Bot replies:

- Missing image: Hãy gửi ảnh vé số.
- Cannot read ticket fields: Không đọc được vé số.
- No win: includes ticket number and mapped province display name.
- Win: returns matched prize lines.

## Important Implementation Notes

- Province slug from OpenAI is used for result fetch.
- Province display name is resolved with ProvinceMap::get() for user-facing text.
- Prize checking logic is suffix-based using str_ends_with().
	- This matches Vietnam lottery checking behavior where ticket suffix can win specific prizes.

## Security and Reliability Notes

Current code works but has hardening opportunities:

- TelegramService disables SSL verification.
	- Recommended: enable peer and host verification in production.
- OpenAIService has no explicit error handling for:
	- curl_exec failure
	- non-200 responses
	- invalid JSON structure
- LotteryService uses file_get_contents() without timeout/retry handling.
- Config loader assumes app/.env always exists and is valid.

## Suggested Next Improvements

1. Add robust error handling and retries for all external APIs.
2. Add request timeouts for every HTTP call.
3. Add input validation for province_slug against ProvinceMap::all().
4. Add unit tests for PrizeChecker and OpenAI response parsing.
5. Add idempotency protection for duplicate Telegram updates.
6. Add lightweight dependency injection to make services easier to test.

## Quick Start Checklist

1. Create app/.env with TELEGRAM_BOT_TOKEN and OPENAI_API_KEY.
2. Start container with docker compose up -d --build.
3. Expose public URL to app/public/webhook.php.
4. Register Telegram webhook.
5. Send a ticket image to your bot.
6. Inspect logs/telegram.log when debugging.
