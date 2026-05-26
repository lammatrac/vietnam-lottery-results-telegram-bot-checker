<?php

namespace App\Services;

use App\Config;

class OpenAIService
{
    public static function extract(
        string $imageUrl
    ): array {

        $apiKey = Config::get('OPENAI_API_KEY');

        $payload = [
            'model' => 'gpt-4.1-mini',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' =>
                                'Extract Vietnam lottery ticket info. Return JSON only: province_slug, ticket_number'
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $imageUrl
                            ]
                        ]
                    ]
                ]
            ],
            'response_format' => [
                'type' => 'json_object'
            ]
        ];

        $ch = curl_init(
            'https://api.openai.com/v1/chat/completions'
        );

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

        $response = curl_exec($ch);

        curl_close($ch);

        $json = json_decode($response, true);

        return json_decode(
            $json['choices'][0]['message']['content'],
            true
        );
    }
}
