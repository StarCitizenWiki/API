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
        0.125 => [0.625, 0.625, 0.625],
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

        return array_find_key(self::BOXES, fn ($box) => $box[0] <= $max[0] && $box[1] <= $max[1] && $box[2] <= $max[2]);
    }

    /**
     * Find the largest standard SCU box (max 32) that fits within both the grid interior
     * dimensions and the max item size accepted by the container.
     *
     * @param  array{x: float, y: float, z: float}  $interior  Grid interior dimensions (X, Z, Y in game data).
     * @param  array{x: float, y: float, z: float}  $maxSize  Max item dimensions accepted (MaxSize in game data).
     * @return int|null SCU value (1, 2, 4, 8, 16, or 32), or null if nothing fits.
     */
    public static function largestThatFitsInGrid(array $interior, array $maxSize): ?int
    {
        $interiorDims = [$interior['x'], $interior['y'], $interior['z']];
        $maxDims = [$maxSize['x'], $maxSize['y'], $maxSize['z']];
        sort($interiorDims);
        sort($maxDims);

        $effective = [
            min($interiorDims[0], $maxDims[0]),
            min($interiorDims[1], $maxDims[1]),
            min($interiorDims[2], $maxDims[2]),
        ];

        return array_find_key(self::BOXES, fn ($box) => $box[0] <= $effective[0] && $box[1] <= $effective[1] && $box[2] <= $effective[2]);
    }

    /**
     * Find the smallest standard SCU box whose dimensions are all at least as large
     * as the given min item size.
     *
     * @param  array{x: float, y: float, z: float}  $minSize
     * @return int|null SCU value (1, 2, 4, 8, 16, or 32), or null if nothing fits.
     */
    public static function smallestThatFits(array $minSize): ?int
    {
        $min = [$minSize['x'], $minSize['y'], $minSize['z']];
        sort($min);

        return array_find_key(array_reverse(self::BOXES, preserve_keys: true), fn ($box) => $box[0] >= $min[0] && $box[1] >= $min[1] && $box[2] >= $min[2]);
    }
}
