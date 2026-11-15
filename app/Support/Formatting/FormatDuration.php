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

        return self::interval($normalizedSeconds, options: ['parts' => 2]);
    }

    public static function interval(int $value, string $unit = 'seconds', array $options = [], ?string $locale = null): string
    {
        $interval = CarbonInterval::make($value, $unit);

        if ($locale !== null) {
            $interval->locale($locale);
        }

        return $interval->cascade()->forHumans($options);
    }
}
