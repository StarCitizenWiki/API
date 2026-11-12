<?php

declare(strict_types=1);

namespace App\Support;

class ScuBox
{
    /**
     * Standard SCU box dimensions (sorted ascending per box).
     * Each box can be rotated, so we sort to compare against sorted max dims.
     * SCU lives in the value because PHP casts a non-integral float key (0.125) to int 0.
     *
     * @var list<array{scu: int|float, dims: array{float, float, float}}>
     */
    private const array BOXES = [
        ['scu' => 32, 'dims' => [2.5, 5.0, 5.0]],
        ['scu' => 16, 'dims' => [2.5, 2.5, 5.0]],
        ['scu' => 8, 'dims' => [2.5, 2.5, 2.5]],
        ['scu' => 4, 'dims' => [1.25, 2.5, 2.5]],
        ['scu' => 2, 'dims' => [1.25, 1.25, 2.5]],
        ['scu' => 1, 'dims' => [1.25, 1.25, 1.25]],
        ['scu' => 0.125, 'dims' => [0.625, 0.625, 0.625]],
    ];

    /**
     * Find the largest standard SCU box (max 32) that fits within the given max item size.
     *
     * @param  array{x: float, y: float, z: float}  $maxSize
     * @return int|float|null SCU value (0.125, 1, 2, 4, 8, 16, or 32), or null if nothing fits.
     */
    public static function largestThatFits(array $maxSize): int|float|null
    {
        $max = [$maxSize['x'], $maxSize['y'], $maxSize['z']];
        sort($max);

        $box = array_find(self::BOXES, fn (array $box) => $box['dims'][0] <= $max[0] && $box['dims'][1] <= $max[1] && $box['dims'][2] <= $max[2]);

        return $box === null ? null : $box['scu'];
    }

    /**
     * Find the largest standard SCU box (max 32) that fits within both the grid interior
     * dimensions and the max item size accepted by the container.
     *
     * @param  array{x: float, y: float, z: float}  $interior  Grid interior dimensions (X, Z, Y in game data).
     * @param  array{x: float, y: float, z: float}  $maxSize  Max item dimensions accepted (MaxSize in game data).
     * @return int|float|null SCU value (0.125, 1, 2, 4, 8, 16, or 32), or null if nothing fits.
     */
    public static function largestThatFitsInGrid(array $interior, array $maxSize): int|float|null
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

        $box = array_find(self::BOXES, fn (array $box) => $box['dims'][0] <= $effective[0] && $box['dims'][1] <= $effective[1] && $box['dims'][2] <= $effective[2]);

        return $box === null ? null : $box['scu'];
    }

    /**
     * Find the smallest standard SCU box whose dimensions are all at least as large
     * as the given min item size.
     *
     * @param  array{x: float, y: float, z: float}  $minSize
     * @return int|float|null SCU value (0.125, 1, 2, 4, 8, 16, or 32), or null if nothing fits.
     */
    public static function smallestThatFits(array $minSize): int|float|null
    {
        $min = [$minSize['x'], $minSize['y'], $minSize['z']];
        sort($min);

        $box = array_find(array_reverse(self::BOXES), fn (array $box) => $box['dims'][0] >= $min[0] && $box['dims'][1] >= $min[1] && $box['dims'][2] >= $min[2]);

        return $box === null ? null : $box['scu'];
    }
}
