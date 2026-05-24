<?php

declare(strict_types=1);

namespace App\Support;

class Format
{
    /**
     * Nonbreaking space (U+00A0) used as thousands separator.
     */
    private const THOUSANDS_SEP = "\u{00A0}";

    /**
     * Return a CSS color class based on the sign of the value.
     *
     * Positive -> text-error, negative -> text-success (or inverted).
     */
    public static function colorClass(float|int|null $value, ?bool $invert = false): string
    {
        if (empty($value)) {
            return '';
        }

        if ($invert) {
            return $value < 0 ? 'text-error' : 'text-success';
        }

        return match ($value <=> 0) {
            1 => 'text-error',
            0 => '',
            -1 => 'text-success',
        };
    }

    /**
     * Format a number with a given number of decimal places.
     */
    public static function number(float|int $value, int $decimals = 0): string
    {
        return number_format($value, $decimals, '.', self::THOUSANDS_SEP);
    }

    /**
     * Format a number, or return a dash if null.
     */
    public static function numberOrDash(float|int|null $value, int $decimals = 0): string
    {
        if ($value === null) {
            return '-';
        }

        return self::number($value, $decimals);
    }

    /**
     * Format a value as a percentage string, or return a dash if null.
     * Multiplies the value by 100.
     */
    public static function percentOrDash(float|int|null $value, int $decimals = 1): string
    {
        if ($value === null) {
            return '-';
        }

        return number_format((float) $value * 100, $decimals, '.', self::THOUSANDS_SEP).'%';
    }

    /**
     * Format a number in compact form with suffixes for large (k/M/B/T)
     * and small (m/μ/n) values.
     */
    public static function compact(float|int $value, int $maxDecimals = 2): string
    {
        $v = (float) $value;

        if (! is_finite($v)) {
            return is_nan($v) ? 'NAN' : ($v > 0 ? 'INF' : '-INF');
        }

        if ($v == 0.0) {
            return '0';
        }

        $sign = $v < 0 ? '-' : '';
        $abs = abs($v);

        $micro = "\u{03BC}"; // μ

        // Large suffixes (divide)
        $large = [
            ['threshold' => 1_000_000_000_000, 'div' => 1_000_000_000_000, 'suffix' => 'T'],
            ['threshold' => 1_000_000_000, 'div' => 1_000_000_000, 'suffix' => 'B'],
            ['threshold' => 1_000_000, 'div' => 1_000_000, 'suffix' => 'M'],
            ['threshold' => 1_000, 'div' => 1_000, 'suffix' => 'k'],
        ];

        // Small prefixes (multiply) - appended to the number (e.g., 10μ)
        $small = [
            ['mult' => 1_000, 'suffix' => 'm'],
            ['mult' => 1_000_000, 'suffix' => $micro],
            ['mult' => 1_000_000_000, 'suffix' => 'n'],
        ];

        $mode = 'none';   // none | large | small
        $suffix = '';
        $div = 1.0;
        $mult = 1.0;

        // Choose large suffix
        foreach ($large as $opt) {
            if ($abs >= $opt['threshold']) {
                $mode = 'large';
                $suffix = $opt['suffix'];
                $div = (float) $opt['div'];

                break;
            }
        }

        // Choose small prefix (only when abs < 0.001)
        if ($mode === 'none' && $abs > 0.0 && $abs < 0.001) {
            $mode = 'small';

            // Prefer the first prefix that yields [1, 999], otherwise fall back to the smallest prefix.
            $picked = null;
            foreach ($small as $opt) {
                $scaled = $abs * $opt['mult'];
                if ($scaled >= 1.0 && $scaled < 1000.0) {
                    $picked = $opt;

                    break;
                }
            }
            if ($picked === null) {
                $picked = $small[array_key_last($small)];
            }

            $suffix = $picked['suffix'];
            $mult = (float) $picked['mult'];
        }

        $roundSig = static function (float $x, int $sigDigits, int $maxDecimals): array {
            if ($x == 0.0) {
                return [0.0, 0];
            }

            $digits = (int) floor(log10($x));
            $decimals = $sigDigits - $digits - 1;

            if ($decimals < 0) {
                $decimals = 0;
            } elseif ($decimals > $maxDecimals) {
                $decimals = $maxDecimals;
            }

            $rounded = round($x, $decimals);

            // Avoid negative zero artifacts
            if ($rounded == 0.0) {
                $rounded = 0.0;
            }

            return [$rounded, $decimals];
        };

        $formatNumber = static function (float $x, int $decimals): string {
            $s = number_format($x, $decimals, '.', '');

            if ($decimals > 0) {
                $s = rtrim($s, '0');
                $s = rtrim($s, '.');
            }

            return $s;
        };

        // Normalize after rounding to avoid outputs like "1000k" or "1000μ".
        for ($i = 0; $i < 5; $i++) {
            $scaled = match ($mode) {
                'large' => $abs / $div,
                'small' => $abs * $mult,
                default => $abs,
            };

            [$rounded, $decimals] = $roundSig($scaled, 3, $maxDecimals);

            // Large: if rounding bumps to 1000, promote suffix (k -> M -> B)
            if ($mode === 'large' && $rounded >= 1000.0) {
                if ($suffix === 'k') {
                    $suffix = 'M';
                    $div = 1_000_000.0;

                    continue;
                }
                if ($suffix === 'M') {
                    $suffix = 'B';
                    $div = 1_000_000_000.0;

                    continue;
                }
                // 'B' has no higher suffix in this spec; keep as-is.
            }

            // Small: if rounding bumps to 1000, step toward a larger unit prefix (n -> μ -> m -> none)
            if ($mode === 'small' && $rounded >= 1000.0) {
                if ($suffix === 'n') {
                    $suffix = $micro;
                    $mult = 1_000_000.0;

                    continue;
                }
                if ($suffix === $micro) {
                    $suffix = 'm';
                    $mult = 1_000.0;

                    continue;
                }
                if ($suffix === 'm') {
                    $mode = 'none';
                    $suffix = '';
                    $mult = 1.0;

                    continue;
                }
            }

            $num = $formatNumber($rounded, $decimals);
            if ($num === '0') {
                return '0';
            }

            return $sign.$num.$suffix;
        }

        // Fallback (should not be hit)
        return $sign.(string) $abs.$suffix;
    }

    /**
     * Format a value with a unit, respecting spacing rules for /s and %.
     */
    public static function valueWithUnit(float|int|null $value, string $unit, int $decimals, bool $compact = false, ?bool $sign = false): string
    {
        if ($value === null) {
            return '-';
        }

        $num = $compact ? self::compact($value) : self::number($value, $decimals);

        $prefix = '';
        if ($sign && $value > 0) {
            $prefix = '+';
        }

        // Space rule: no space for /s, % or empty unit, space otherwise
        if ($unit === '/s' || $unit === '%' || $unit === '') {
            return $prefix.$num.$unit;
        }

        return $prefix.$num.' '.$unit;
    }

    /**
     * Format a range (min-max) with a unit.
     * If both are equal, renders a single value. Handles null bounds with ≥/≤.
     */
    public static function range(?float $min, ?float $max, string $unit, int $decimals = 0, bool $compact = false): string
    {
        if (abs($min - $max) < 0.000001) {
            return self::valueWithUnit($min, $unit, $decimals, $compact);
        }

        if ($min !== null && $max !== null) {
            $a = $compact ? self::compact($min) : self::number($min, $decimals);
            $b = $compact ? self::compact($max) : self::number($max, $decimals);

            if ($unit === '/s' || $unit === '%') {
                return $a.' - '.$b.$unit;
            }

            return $a.' - '.$b.' '.$unit;
        }

        if ($min !== null) {
            $a = $compact ? self::compact($min) : self::number($min, $decimals);

            if ($unit === '/s' || $unit === '%') {
                return '≥ '.$a.$unit;
            }

            return '≥ '.$a.' '.$unit;
        }

        if ($max !== null) {
            $b = $compact ? self::compact($max) : self::number($max, $decimals);

            if ($unit === '/s' || $unit === '%') {
                return '≤ '.$b.$unit;
            }

            return '≤ '.$b.' '.$unit;
        }

        return '-';
    }

    /**
     * Format a container size value.
     * Returns 'Unlimited' for negative, casts to int for positive.
     */
    public static function containerSize(?float $value): string
    {
        if ($value === null) {
            return '-';
        }

        if ($value < 0) {
            return 'Unlimited';
        }

        return (string) (int) $value;
    }

    /**
     * Format a scaled value with trailing zeros removed.
     *
     * Divides the value by `$divisor`, formats with `$decimals` decimal places,
     * then strips trailing zeros and the trailing decimal point.
     */
    private static function formatScaled(float $value, int|float $divisor, int $decimals): string
    {
        $formatted = self::number($value / $divisor, $decimals);
        $formatted = rtrim($formatted, '0');

        return rtrim($formatted, '.');
    }

    /**
     * Format a distance in meters as gigameters (GM).
     *
     * Returns a human-readable string like "~56 GM", "1.2 GM", or the
     * raw meter value with unit when below 0.05 GM (50,000 km).
     *
     * @param  float|int|null  $meters  Distance in meters
     * @param  int  $decimals  Maximum decimal places (default 1)
     */
    public static function gigameters(float|int|null $meters, int $decimals = 1): ?string
    {
        if ($meters === null) {
            return null;
        }

        $gm = (float) $meters / 1_000_000_000;

        if ($gm > 1_000_000_000) {
            return 'Unlimited';
        }

        if ($gm >= 0.05) {
            return '~'.self::formatScaled((float) $meters, 1_000_000_000, $decimals).' GM';
        }

        $km = (float) $meters / 1000;
        if ($km >= 1) {
            return self::number((int) round($km)).' km';
        }

        return self::number((int) round($meters)).' m';
    }

    /**
     * Format a velocity or acceleration into the most readable SI prefix.
     *
     * >= 1 Mm -> Mm{suffix}  (e.g. 165 Mm/s)
     * >= 1 km -> km{suffix}  (e.g. 12.5 km/s²)
     * else   -> m{suffix}   (e.g. 500 m/s)
     */
    private static function formatScaledVelocity(float $value, int $decimals, string $suffix, bool $isNull): ?string
    {
        if ($isNull) {
            return null;
        }

        if ($value >= 1_000_000) {
            return self::formatScaled($value, 1_000_000, $decimals).' Mm'.$suffix;
        }

        if ($value >= 1_000) {
            return self::formatScaled($value, 1_000, $decimals).' km'.$suffix;
        }

        return self::number($value, 0).' m'.$suffix;
    }

    /**
     * Format a velocity from m/s into the most readable SI prefix.
     *
     * @param  float|int|null  $metersPerSecond  Velocity in m/s
     * @param  int  $decimals  Maximum decimal places (default 1)
     */
    public static function velocity(float|int|null $metersPerSecond, int $decimals = 1): ?string
    {
        return self::formatScaledVelocity((float) $metersPerSecond, $decimals, '/s', $metersPerSecond === null);
    }

    /**
     * Format an acceleration from m/s² into the most readable SI prefix.
     *
     * @param  float|int|null  $metersPerSecondSquared  Acceleration in m/s²
     * @param  int  $decimals  Maximum decimal places (default 1)
     */
    public static function acceleration(float|int|null $metersPerSecondSquared, int $decimals = 1): ?string
    {
        return self::formatScaledVelocity((float) $metersPerSecondSquared, $decimals, '/s²', $metersPerSecondSquared === null);
    }

    /**
     * Format a value as a signed percentage, where the input is a ratio (1.0 = 0%).
     */
    public static function signedPercent(mixed $value, bool $showPlus = true): string
    {
        if ($value === null) {
            return '-';
        }

        $pct = ((float) $value - 1) * 100;

        if ($pct > 0) {
            return ($showPlus ? '+' : '').number_format($pct, 0, '.', self::THOUSANDS_SEP).'%';
        }

        if ($pct < 0) {
            return number_format($pct, 0, '.', self::THOUSANDS_SEP).'%';
        }

        return '0%';
    }
}
