<?php

namespace App\Support\Hajj;

use Illuminate\Support\Facades\Cache;

class HajjImportProgress
{
    private static function key(string $token): string
    {
        return "hajj_import:{$token}";
    }

    public static function start(string $token, ?int $userId): void
    {
        Cache::put(self::key($token), [
            'status' => 'queued',
            'user_id' => $userId,
            'processed' => 0,
            'total' => 0,
        ], now()->addHour());
    }

    public static function processing(string $token, int $processed, int $total): void
    {
        $current = Cache::get(self::key($token), []);

        Cache::put(self::key($token), [
            ...$current,
            'status' => 'processing',
            'processed' => $processed,
            'total' => $total,
        ], now()->addHour());
    }

    /**
     * @param  array{imported: int, replaced: int, skipped: int, errors: list<string>}  $summary
     */
    public static function completed(string $token, array $summary): void
    {
        $current = Cache::get(self::key($token), []);

        Cache::put(self::key($token), [
            ...$current,
            'status' => 'completed',
            'summary' => $summary,
        ], now()->addHour());
    }

    public static function failed(string $token, string $message): void
    {
        $current = Cache::get(self::key($token), []);

        Cache::put(self::key($token), [
            ...$current,
            'status' => 'failed',
            'message' => $message,
        ], now()->addHour());
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get(string $token): ?array
    {
        $value = Cache::get(self::key($token));

        return is_array($value) ? $value : null;
    }

    public static function forget(string $token): void
    {
        Cache::forget(self::key($token));
    }
}
