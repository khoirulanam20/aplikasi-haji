<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IconoirIconHtml implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value)) {
            $fail('Format icon tidak valid.');

            return;
        }

        $allowed = collect(config('iconoir.icons', []))
            ->pluck('name')
            ->map(fn (string $name) => preg_quote($name, '/'))
            ->implode('|');

        if ($allowed === '') {
            $fail('Daftar icon belum dikonfigurasi.');

            return;
        }

        $pattern = '/^<i\s+class="iconoir-(?:'.$allowed.')(?:\s[^"]*)?"\s*><\/i>$/i';

        if (! preg_match($pattern, trim($value))) {
            $fail('Icon harus dipilih dari daftar Iconoir yang tersedia.');
        }
    }
}
