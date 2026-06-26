<?php

namespace App\Support\Hajj;

class HajjTahunOptions
{
    public const MIN = 2000;

    public const MAX = 2100;

    /**
     * @return list<int>
     */
    public static function values(): array
    {
        $end = min(self::MAX, (int) date('Y') + 5);

        return array_values(range($end, self::MIN));
    }
}
