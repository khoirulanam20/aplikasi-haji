<?php

namespace App\Support\Settings;

use App\Models\WebSetting;

class ThemeSettingsService
{
    public function cssVariables(?WebSetting $setting = null): array
    {
        $setting ??= WebSetting::query()->first();

        $primary = $this->normalizeHex($setting?->primary_color, '#FF5B37');
        $secondary = $this->normalizeHex($setting?->secondary_color, '#4B5694');

        return [
            'primary' => $primary,
            'primary_hover' => $this->darken($primary, 8),
            'secondary' => $secondary,
            'secondary_hover' => $this->darken($secondary, 8),
        ];
    }

    private function normalizeHex(?string $color, string $fallback): string
    {
        $color = strtoupper(trim((string) $color));

        if (preg_match('/^#[0-9A-F]{6}$/', $color)) {
            return $color;
        }

        return strtoupper($fallback);
    }

    private function darken(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $factor = max(0, min(100, 100 - $percent)) / 100;

        return sprintf(
            '#%02X%02X%02X',
            (int) round($r * $factor),
            (int) round($g * $factor),
            (int) round($b * $factor),
        );
    }
}
