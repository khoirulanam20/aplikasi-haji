<?php

namespace App\Support;

class FlashMessages
{
    /**
     * @return array{type: string, title: string, message: string}|null
     */
    public static function resolve(?string $status): ?array
    {
        if ($status === null || $status === '') {
            return null;
        }

        $configured = config('flash_messages.'.$status);

        if (is_array($configured)) {
            return $configured;
        }

        return [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => $status,
        ];
    }
}
