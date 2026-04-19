<?php

declare(strict_types=1);

function color_class(float|int|null $value, ?bool $invert = false): string
{
    if ($value === null) {
        return '';
    }

    if ($value === 0.0) {
        return '';
    }

    if ($invert) {
        return $value < 0 ? 'text-warning' : 'text-success';
    }

    return match ($value <=> 0) {
        1 => 'text-warning',
        0 => '',
        -1 => 'text-success',
    };
}

function fmt(float|int $value, int $decimals = 0): string
{
    return number_format($value, $decimals);
}

function fmt_or_dash(float|int|null $value, int $decimals = 0): string
{
    if ($value === null) {
        return '—';
    }

    return fmt($value, $decimals);
}

function fmt_compact(float|int $value, int $maxDecimals = 2): string
{
    $v = (float) $value;

    if (! is_finite($v)) {
        return (string) $value;
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
        ['threshold' => 1_000_000,     'div' => 1_000_000,     'suffix' => 'M'],
        ['threshold' => 1_000,         'div' => 1_000,         'suffix' => 'k'],
    ];

    // Small prefixes (multiply) - appended to the number (e.g., 10μ)
    $small = [
        ['mult' => 1_000,         'suffix' => 'm'],
        ['mult' => 1_000_000,     'suffix' => $micro],
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

function fmt_value_with_unit(float|int|null $value, string $unit, int $decimals, bool $compact = false, ?bool $sign = false): string
{
    if ($value === null) {
        return '-';
    }

    $num = $compact ? fmt_compact($value) : fmt($value, $decimals);

    // Space rule: no space for /s or %, space otherwise
    if ($unit === '/s' || $unit === '%') {
        return $num.$unit;
    }

    $prefix = '';
    if ($sign && $value > 0) {
        $prefix = '+';
    }

    return $prefix.$num.' '.$unit;
}

function fmt_range(?float $min, ?float $max, string $unit, int $decimals = 0, bool $compact = false): string
{
    if (abs($min - $max) < 0.000001) {
        return fmt_value_with_unit($min, $unit, $decimals, $compact);
    }

    if ($min !== null && $max !== null) {
        $a = $compact ? fmt_compact($min) : fmt($min, $decimals);
        $b = $compact ? fmt_compact($max) : fmt($max, $decimals);

        if ($unit === '/s' || $unit === '%') {
            return $a.'-'.$b.$unit;
        }

        return $a.'-'.$b.' '.$unit;
    }

    if ($min !== null) {
        $a = $compact ? fmt_compact($min) : fmt($min, $decimals);

        if ($unit === '/s' || $unit === '%') {
            return '≥ '.$a.$unit;
        }

        return '≥ '.$a.' '.$unit;
    }

    if ($max !== null) {
        $b = $compact ? fmt_compact($max) : fmt($max, $decimals);

        if ($unit === '/s' || $unit === '%') {
            return '≤ '.$b.$unit;
        }

        return '≤ '.$b.' '.$unit;
    }

    return '—';
}

function fmt_container_size(?float $value): string
{
    if ($value === null) {
        return '—';
    }

    if ($value < 0) {
        return 'Unlimited';
    }

    return (string) (int) $value;
}
