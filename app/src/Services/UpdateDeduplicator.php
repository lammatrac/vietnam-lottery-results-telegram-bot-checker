<?php

namespace App\Services;

class UpdateDeduplicator
{
    private const STORE_FILE = '/logs/processed_update_ids.json';

    public static function isDuplicate(
        int|string $updateId,
        int $ttlSeconds = 43200
    ): bool {

        $id = (string) $updateId;

        if ($id === '') {

            return false;
        }

        $fp = fopen(self::STORE_FILE, 'c+');

        if ($fp === false) {

            return false;
        }

        $duplicate = false;

        try {

            if (!flock($fp, LOCK_EX)) {

                return false;
            }

            rewind($fp);

            $raw = stream_get_contents($fp);

            $data = $raw
                ? json_decode($raw, true)
                : [];

            if (!is_array($data)) {

                $data = [];
            }

            $now = time();

            foreach ($data as $savedId => $savedAt) {

                $savedAt = is_numeric($savedAt)
                    ? (int) $savedAt
                    : 0;

                if ($savedAt <= 0 || ($now - $savedAt) > $ttlSeconds) {

                    unset($data[$savedId]);
                }
            }

            $duplicate = isset($data[$id]);

            if (!$duplicate) {

                $data[$id] = $now;
            }

            rewind($fp);
            ftruncate($fp, 0);
            fwrite($fp, json_encode($data));
            fflush($fp);
            flock($fp, LOCK_UN);

        } finally {

            fclose($fp);
        }

        return $duplicate;
    }
}
