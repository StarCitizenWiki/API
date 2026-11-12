<?php

declare(strict_types=1);

use App\Http\Resources\Game\Item\ItemInventoryResource;
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

    it('returns 2 SCU for Pisces cargo grid (1.25 x 2.5 x 1.25)', function (): void {
        expect(ScuBox::largestThatFits(['x' => 1.25, 'y' => 2.5, 'z' => 1.25]))->toBe(2);
    });
});

describe('1/8 SCU box (0.625³)', function (): void {
    it('returns 0.125 from largestThatFits for a 1.0³ grid (regression: the 0.125 key was cast to 0)', function (): void {
        expect(ScuBox::largestThatFits(['x' => 1.0, 'y' => 1.0, 'z' => 1.0]))->toBe(0.125);
    });

    it('returns 0.125 from largestThatFitsInGrid when only the 1/8 box fits', function (): void {
        expect(ScuBox::largestThatFitsInGrid(
            ['x' => 1.0, 'y' => 1.0, 'z' => 1.0],
            ['x' => 2.0, 'y' => 2.0, 'z' => 2.0]
        ))->toBe(0.125);
    });

    it('returns 0.125 from smallestThatFits when only the 1/8 box satisfies the min size', function (): void {
        expect(ScuBox::smallestThatFits(['x' => 0.5, 'y' => 0.5, 'z' => 0.5]))->toBe(0.125);
    });
});

describe('ItemInventoryResource scu box output', function (): void {
    it('emits min_scu_box 0.125 and max_scu_box 0.125 for a micro-SCU container', function (): void {
        // Decision on the `!== 1` suppression (ItemInventoryResource.php:115): keep it.
        // It hides only the common 1 SCU default min box; 0.125 is a real signal for
        // micro-SCU containers and must be emitted (pre-fix it leaked through as 0).
        $result = (new ItemInventoryResource([
            'MinSize' => ['X' => 0.5, 'Y' => 0.5, 'Z' => 0.5],
            'MaxSize' => ['X' => 1.0, 'Y' => 1.0, 'Z' => 1.0],
        ]))->resolve();

        expect($result['min_scu_box'])->toBe(0.125)
            ->and($result['max_scu_box'])->toBe(0.125);
    });
});
