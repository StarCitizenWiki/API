<?php

declare(strict_types=1);

use App\Support\ScuBox;

describe('largest that fits', function (): void {
    it('returns 1 SCU for 1.25³', function (): void {
        expect(ScuBox::largestThatFits(['x' => 1.25, 'y' => 1.25, 'z' => 1.25]))->toBe(1);
    });

    it('returns 2 SCU for 1.25 × 1.25 × 2.5', function (): void {
        expect(ScuBox::largestThatFits(['x' => 1.25, 'y' => 1.25, 'z' => 2.5]))->toBe(2);
    });

    it('returns 4 SCU for 1.25 × 2.5 × 2.5 (volume would suggest 8)', function (): void {
        expect(ScuBox::largestThatFits(['x' => 1.25, 'y' => 2.5, 'z' => 2.5]))->toBe(4);
    });

    it('returns 8 SCU for 2.5³', function (): void {
        expect(ScuBox::largestThatFits(['x' => 2.5, 'y' => 2.5, 'z' => 2.5]))->toBe(8);
    });

    it('returns 16 SCU for 2.5 × 2.5 × 5', function (): void {
        expect(ScuBox::largestThatFits(['x' => 2.5, 'y' => 2.5, 'z' => 5]))->toBe(16);
    });

    it('returns 32 SCU for 2.5 × 5 × 5', function (): void {
        expect(ScuBox::largestThatFits(['x' => 2.5, 'y' => 5, 'z' => 5]))->toBe(32);
    });

    it('caps at 32 SCU even for 5³', function (): void {
        expect(ScuBox::largestThatFits(['x' => 5, 'y' => 5, 'z' => 5]))->toBe(32);
    });

    it('handles unsorted dimensions', function (): void {
        expect(ScuBox::largestThatFits(['x' => 2.5, 'y' => 1.25, 'z' => 2.5]))->toBe(4);
    });

    it('returns null when nothing fits', function (): void {
        expect(ScuBox::largestThatFits(['x' => 0.5, 'y' => 0.5, 'z' => 0.5]))->toBeNull();
    });

    it('returns 2 SCU for Pisces cargo grid (1.25 × 2.5 × 1.25)', function (): void {
        expect(ScuBox::largestThatFits(['x' => 1.25, 'y' => 2.5, 'z' => 1.25]))->toBe(2);
    });
});
