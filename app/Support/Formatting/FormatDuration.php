<?php

declare(strict_types=1);

namespace App\Support\Formatting;

use Carbon\CarbonInterval;

class FormatDuration
{
    public static function fromSeconds(mixed $seconds): ?string
    {
        if (! is_numeric($seconds)) {
            return null;
        }

        $normalizedSeconds = max(0, (int) round((float) $seconds));

        if ($normalizedSeconds <= 60) {
            return $normalizedSeconds.' seconds';
        }

        return CarbonInterval::seconds($normalizedSeconds)->cascade()->forHumans([
            'parts' => 2,
        ]);
    }
}
