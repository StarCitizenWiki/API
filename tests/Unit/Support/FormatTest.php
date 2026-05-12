<?php

declare(strict_types=1);

use App\Support\Format;

describe('colorClass', function (): void {
    it('returns empty string for null', function (): void {
        expect(Format::colorClass(null))->toBe('');
    });

    it('returns empty string for zero', function (): void {
        expect(Format::colorClass(0))->toBe('');
    });

    it('returns text-error for positive values', function (): void {
        expect(Format::colorClass(5))->toBe('text-error');
    });

    it('returns text-success for negative values', function (): void {
        expect(Format::colorClass(-3))->toBe('text-success');
    });

    it('returns text-success for positive when inverted', function (): void {
        expect(Format::colorClass(5, true))->toBe('text-success');
    });

    it('returns text-error for negative when inverted', function (): void {
        expect(Format::colorClass(-3, true))->toBe('text-error');
    });

    it('returns empty string for zero when inverted', function (): void {
        expect(Format::colorClass(0, true))->toBe('');
    });
});

describe('number', function (): void {
    it('formats integer with no decimals', function (): void {
        expect(Format::number(1000))->toBe("1\u{00A0}000");
    });

    it('formats with specified decimals', function (): void {
        expect(Format::number(1234.567, 2))->toBe("1\u{00A0}234.57");
    });

    it('formats zero', function (): void {
        expect(Format::number(0))->toBe('0');
    });

    it('formats negative number', function (): void {
        expect(Format::number(-42, 1))->toBe('-42.0');
    });
});

describe('numberOrDash', function (): void {
    it('returns dash for null', function (): void {
        expect(Format::numberOrDash(null))->toBe('-');
    });

    it('formats non-null value', function (): void {
        expect(Format::numberOrDash(42, 2))->toBe('42.00');
    });

    it('formats zero', function (): void {
        expect(Format::numberOrDash(0))->toBe('0');
    });
});

describe('compact', function (): void {
    it('returns zero for zero', function (): void {
        expect(Format::compact(0))->toBe('0');
    });

    it('formats thousands', function (): void {
        expect(Format::compact(1500))->toBe('1.5k');
    });

    it('formats millions', function (): void {
        expect(Format::compact(2_500_000))->toBe('2.5M');
    });

    it('formats billions', function (): void {
        expect(Format::compact(3_000_000_000))->toBe('3B');
    });

    it('formats trillions', function (): void {
        expect(Format::compact(1_000_000_000_000))->toBe('1T');
    });

    it('formats sub-milli value with micro prefix', function (): void {
        expect(Format::compact(0.0005))->toBe('500μ');
    });

    it('formats very small value with micro prefix', function (): void {
        expect(Format::compact(0.000005))->toBe('5μ');
    });

    it('formats extremely small value with nano prefix', function (): void {
        expect(Format::compact(0.000000005))->toBe('5n');
    });

    it('formats negative values', function (): void {
        expect(Format::compact(-1500))->toBe('-1.5k');
    });

    it('returns string for INF', function (): void {
        expect(Format::compact(INF))->toBe('INF');
    });

    it('formats values just below 1000 without suffix', function (): void {
        expect(Format::compact(999))->toBe('999');
    });

    it('formats boundary at 1000 with k suffix', function (): void {
        $result = Format::compact(1000);
        expect($result)->toBe('1k');
    });

    it('respects maxDecimals parameter', function (): void {
        $result = Format::compact(1234, 0);
        expect($result)->toBe('1k');
    });

    it('formats 500 without suffix', function (): void {
        expect(Format::compact(500))->toBe('500');
    });

    it('formats values near rounding boundary', function (): void {
        // 999999 should round to 1M
        expect(Format::compact(999_999))->toBe('1M');
    });
    it('returns NAN for NAN input', function (): void {
        expect(Format::compact(NAN))->toBe('NAN');
    });
});

describe('valueWithUnit', function (): void {
    it('returns dash for null', function (): void {
        expect(Format::valueWithUnit(null, 'kg', 0))->toBe('-');
    });

    it('formats with space-separated unit', function (): void {
        expect(Format::valueWithUnit(42, 'kg', 0))->toBe('42 kg');
    });

    it('formats with no space for percent', function (): void {
        expect(Format::valueWithUnit(50, '%', 0))->toBe('50%');
    });

    it('formats with no space for per-second', function (): void {
        expect(Format::valueWithUnit(100, '/s', 1))->toBe('100.0/s');
    });

    it('adds plus sign when sign is true and value positive', function (): void {
        // Sign prefix is only added for non-%/non-/s units
        expect(Format::valueWithUnit(5, 'kg', 1, sign: true))->toBe('+5.0 kg');
    });

    it('does not add plus sign for zero', function (): void {
        expect(Format::valueWithUnit(0, '%', 0, sign: true))->toBe('0%');
    });

    it('does not add plus sign for negative', function (): void {
        expect(Format::valueWithUnit(-5, '%', 0, sign: true))->toBe('-5%');
    });

    it('uses compact format when compact is true', function (): void {
        expect(Format::valueWithUnit(1500, 'kg', 0, compact: true))->toBe('1.5k kg');
    });

    it('handles empty unit without trailing space', function (): void {
        expect(Format::valueWithUnit(42, '', 0))->toBe('42');
    });
});

describe('range', function (): void {
    it('returns single value when min equals max', function (): void {
        expect(Format::range(5.0, 5.0, 'kg', 0))->toBe('5 kg');
    });

    it('formats min-max range with space-separated unit', function (): void {
        expect(Format::range(1.0, 10.0, 'kg', 0))->toBe('1 - 10 kg');
    });

    it('formats min-max range with percent', function (): void {
        expect(Format::range(1.0, 10.0, '%', 0))->toBe('1 - 10%');
    });

    it('formats min-max range with per-second', function (): void {
        expect(Format::range(1.0, 10.0, '/s', 0))->toBe('1 - 10/s');
    });

    it('formats min-only with >= prefix', function (): void {
        expect(Format::range(5.0, null, 'kg', 0))->toBe('≥ 5 kg');
    });

    it('formats max-only with <= prefix', function (): void {
        expect(Format::range(null, 10.0, 'kg', 0))->toBe('≤ 10 kg');
    });

    it('returns dash when both null', function (): void {
        expect(Format::range(null, null, 'kg', 0))->toBe('-');
    });

    it('formats min-only with percent', function (): void {
        expect(Format::range(5.0, null, '%', 0))->toBe('≥ 5%');
    });

    it('formats max-only with per-second', function (): void {
        expect(Format::range(null, 10.0, '/s', 0))->toBe('≤ 10/s');
    });

    it('uses compact format when compact is true', function (): void {
        expect(Format::range(1000.0, 2000.0, 'kg', 0, compact: true))->toBe('1k - 2k kg');
    });
});

describe('containerSize', function (): void {
    it('returns dash for null', function (): void {
        expect(Format::containerSize(null))->toBe('-');
    });

    it('returns Unlimited for negative', function (): void {
        expect(Format::containerSize(-1))->toBe('Unlimited');
    });

    it('returns integer string for positive', function (): void {
        expect(Format::containerSize(42.7))->toBe('42');
    });

    it('returns zero for zero', function (): void {
        expect(Format::containerSize(0))->toBe('0');
    });
});

describe('signedPercent', function (): void {
    it('returns dash for null', function (): void {
        expect(Format::signedPercent(null))->toBe('-');
    });

    it('formats positive change with plus prefix', function (): void {
        expect(Format::signedPercent(1.25))->toBe('+25%');
    });

    it('formats negative change', function (): void {
        expect(Format::signedPercent(0.8))->toBe('-20%');
    });

    it('formats zero change', function (): void {
        expect(Format::signedPercent(1.0))->toBe('0%');
    });

    it('hides plus when showPlus is false', function (): void {
        expect(Format::signedPercent(1.25, false))->toBe('25%');
    });

    it('formats small positive change', function (): void {
        expect(Format::signedPercent(1.05))->toBe('+5%');
    });

    it('formats large positive change', function (): void {
        expect(Format::signedPercent(3.0))->toBe('+200%');
    });
});
