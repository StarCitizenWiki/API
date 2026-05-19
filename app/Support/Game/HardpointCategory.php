<?php

declare(strict_types=1);

namespace App\Support\Game;

/**
 * Defines hardpoint category ordering and card layout.
 */
final class HardpointCategory
{
    /**
     * Single source of truth for hardpoint category layout.
     *
     * Categories with a column are shown in the primary card grid. Categories
     * marked collapsed are rendered in the secondary "Other hardpoints" group.
     *
     * @var array<string, array{column?: positive-int, collapsed?: bool}>
     */
    private const array CATEGORIES = [
        'Docked Vehicles' => ['column' => 1],
        'Weapons' => ['column' => 1],
        'Manned Turrets' => ['column' => 1],
        'Remote Turrets' => ['column' => 1],
        'PDC Turrets' => ['column' => 1],
        'Turrets' => ['column' => 1],
        'Missile & Bomb Racks' => ['column' => 1],
        'Weapon Lockers' => ['column' => 1],
        'Modules' => ['column' => 1],
        'Mining & Salvage' => ['column' => 1],
        'Tractor Beams' => ['column' => 1],
        'EMP' => ['column' => 1],
        'QED' => ['column' => 1],

        'Shields' => ['column' => 2],
        'Armor' => ['column' => 2],
        'Coolers' => ['column' => 2],
        'Power Plants' => ['column' => 2],
        'Flight Controller' => ['column' => 2],

        'Quantum Drives' => ['column' => 3],
        'Counter Measures' => ['column' => 3],
        'Radars' => ['column' => 3],
        'Life Support' => ['column' => 3],
        'Paints' => ['column' => 3],

        'Fuel' => ['collapsed' => true],
        'Thrusters' => ['collapsed' => true],
        'Cargo Grids' => ['collapsed' => true],
        'Controllers' => ['collapsed' => true],
        'Crew Stations' => ['collapsed' => true],
        'Displays' => ['collapsed' => true],
        'Doors & Hatches' => ['collapsed' => true],
        'Relays' => ['collapsed' => true],
        'Landing Systems' => ['collapsed' => true],
        'Docking' => ['collapsed' => true],
        'AI Modules' => ['collapsed' => true],
        'Systems' => ['collapsed' => true],
        'Customization' => ['collapsed' => true],
        'Other' => ['collapsed' => true],
    ];

    /**
     * Primary category labels - always visible, in display order.
     *
     * @return list<string>
     */
    public static function primary(): array
    {
        return collect(self::CATEGORIES)
            ->filter(fn (array $category): bool => isset($category['column']) && ! ($category['collapsed'] ?? false))
            ->keys()
            ->values()
            ->all();
    }

    /**
     * Primary categories grouped into columns for the card layout.
     *
     * Each inner array is ordered top-to-bottom within its column.
     *
     * @return array<int, list<string>>
     */
    public static function columns(): array
    {
        $columns = [];

        foreach (self::CATEGORIES as $label => $category) {
            $column = $category['column'] ?? null;

            if ($column === null || ($category['collapsed'] ?? false)) {
                continue;
            }

            $columns[$column][] = $label;
        }

        ksort($columns);

        return $columns;
    }

    /**
     * All configured category labels in display order.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return array_keys(self::CATEGORIES);
    }
}
