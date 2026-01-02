<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Arr;

/**
 * Trait CalculatesCargoGridSizeLimits
 */
trait CalculatesCargoGridSizeLimits
{
    /**
     * @param  array<int, array<string, mixed>>  $cargoGrids
     * @return array{min_size?: array{x: float|int, y: float|int, z: float|int}, max_size?: array{x: float|int, y: float|int, z: float|int}}|null
     */
    public static function calculateCargoGridSizeLimits(array $cargoGrids): ?array
    {
        $minSize = collect($cargoGrids)
            ->map(fn (array $grid) => self::extractSizeBlock($grid, 'MinSize', 'min_size'))
            ->filter()
            ->sortBy(fn (array $size) => $size['x'] * $size['y'] * $size['z'])
            ->first();

        $maxSize = collect($cargoGrids)
            ->map(fn (array $grid) => self::extractSizeBlock($grid, 'MaxSize', 'max_size'))
            ->filter()
            ->sortByDesc(fn (array $size) => $size['x'] * $size['y'] * $size['z'])
            ->first();

        $limits = array_filter([
            'min_size' => $minSize,
            'max_size' => $maxSize,
        ], static fn ($value) => $value !== null);

        return $limits === [] ? null : $limits;
    }

    /**
     * @param  array<string, mixed>  $grid
     * @return array{x: float|int, y: float|int, z: float|int}|null
     */
    private static function extractSizeBlock(array $grid, string $key, string $fallbackKey): ?array
    {
        $data = Arr::get($grid, $key);

        if (! is_array($data)) {
            $data = Arr::get($grid, $fallbackKey);
        }

        if (! is_array($data)) {
            return null;
        }

        $x = Arr::get($data, 'X', Arr::get($data, 'x'));
        $y = Arr::get($data, 'Y', Arr::get($data, 'y'));
        $z = Arr::get($data, 'Z', Arr::get($data, 'z'));

        if ($x === null || $y === null || $z === null) {
            return null;
        }

        return [
            'x' => $x,
            'y' => $y,
            'z' => $z,
        ];
    }
}
