<?php

declare(strict_types=1);

namespace App\Support\Game;

/**
 * Defines hardpoint category ordering and the primary/collapsed split.
 *
 * Primary categories are always visible. Collapsed categories are hidden.
 */
final class HardpointCategory
{
    /**
     * Primary category labels - always visible, in display order.
     *
     * @return list<string>
     */
    public static function primary(): array
    {
        return [
            'Docked Vehicles',
            'Weapons',
            'Manned Turrets',
            'Remote Turrets',
            'PDC Turrets',
            'Turrets',
            'Missile & Bomb Racks',
            'Shields',
            'Armor',
            'Coolers',
            'Power Plants',
            'Flight Controller',
            'Quantum Drives',
            'Counter Measures',
            'Radars',
            'EMP',
            'QED',
            'Mining',
            'Salvage',
            'Tractor Beams',
            'Towing Beams',
            'Life Support',
        ];
    }

    /**
     * Collapsed category labels
     *
     * @return list<string>
     */
    public static function collapsed(): array
    {
        return [
            'Fuel',
            'Thrusters',
            'Cargo Grids',
            'Controllers',
            'Crew Stations',
            'Displays',
            'Doors & Hatches',
            'Relays',
            'Landing Systems',
            'Docking',
            'AI Modules',
            'Systems',
            'Customization',
            'Paints',
            'Other',
        ];
    }

    /**
     * All category labels in canonical display order.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return array_merge(self::primary(), self::collapsed());
    }
}
