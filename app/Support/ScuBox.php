<?php

declare(strict_types=1);

namespace App\Support;

class ScuBox
{
    /**
     * Standard SCU box dimensions (sorted ascending per box).
     * Each box can be rotated, so we sort to compare against sorted max dims.
     *
     * @var array<int, array{float, float, float}>
     */
    private const array BOXES = [
        32 => [2.5, 5.0, 5.0],
        16 => [2.5, 2.5, 5.0],
        8 => [2.5, 2.5, 2.5],
        4 => [1.25, 2.5, 2.5],
        2 => [1.25, 1.25, 2.5],
        1 => [1.25, 1.25, 1.25],
    ];

    /**
     * Find the largest standard SCU box (max 32) that fits within the given max item size.
     *
     * @param  array{x: float, y: float, z: float}  $maxSize
     * @return int|null SCU value (1, 2, 4, 8, 16, or 32), or null if nothing fits.
     */
    public static function largestThatFits(array $maxSize): ?int
    {
        $max = [$maxSize['x'], $maxSize['y'], $maxSize['z']];
        sort($max);

        return array_find_key(self::BOXES, fn($box) => $box[0] <= $max[0] && $box[1] <= $max[1] && $box[2] <= $max[2]);
    }
}
