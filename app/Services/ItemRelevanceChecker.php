<?php

declare(strict_types=1);

namespace App\Services;

final class ItemRelevanceChecker
{
    /**
     * Determine whether an item is player-relevant based on its name and class_name
     */
    public static function isPlayerRelevant(?string $name, ?string $className): bool
    {
        /**
         * Name-based checks
         */
        if ($name === null || $name === '' || $name === $className) {
            return false;
        }

        $lowerName = strtolower($name);

        if (
            $lowerName === 'test string'
            || $lowerName === 'door control'
            || $lowerName === 'door'
            || str_contains($lowerName, '- name')
            || str_contains($lowerName, 'placeholder')
            || str_starts_with($name, 'PH - ')
            || str_starts_with($name, '[PH]')
            || str_starts_with($name, 'ELD - ')
            || str_starts_with($name, 'Treat Injuries')
            || str_starts_with($name, '@item')
        ) {
            return false;
        }

        /**
         * Class name checks
         */
        if ($className === null || $className === '') {
            return true;
        }

        $lowerClassName = strtolower($className);

        if (
            str_contains($className, '_TEMPLATE')
            || str_ends_with($lowerClassName, '_template')
            || str_ends_with($lowerClassName, '_temp')
            || str_ends_with($lowerClassName, '_templ')
            || str_starts_with($lowerClassName, 'controlpanel_')
        ) {
            return false;
        }

        if (array_any(self::EXCLUDED_PREFIXES, static fn (string $prefix): bool => str_starts_with($lowerClassName, $prefix))) {
            return false;
        }

        if (array_any(self::EXCLUDED_SUFFIXES, static fn (string $suffix): bool => str_ends_with($lowerClassName, $suffix))) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether a vehicle is player-relevant based on its class_name.
     */
    public static function isVehiclePlayerRelevant(string $className): bool
    {
        $lowerClassName = strtolower($className);

        if ($lowerClassName === 'powersuit') {
            return false;
        }

        if (array_any(self::EXCLUDED_VEHICLE_SUFFIXES, static fn (string $suffix): bool => str_ends_with($lowerClassName, $suffix))) {
            return false;
        }

        return true;
    }

    private const array EXCLUDED_PREFIXES = [
        'invisible_',
        'mannequin_',
        'nodraw_',
        'vanduul_',
        'volume_',
        'customizer_',
        'med_body',
        'med_skeleton',
        'test_',
    ];

    private const array EXCLUDED_SUFFIXES = [
        '_vncl',
        '_lowpoly',
        '_securitynetwork',
        '_securitynetwork_weak',
        '_dummy',
        '_active',
        '_debug',
        '_invis',
        '_ai_exclusive',
        '_ai',
        '_dna',
        '_nodna',
        '_hair_extension',
        '_tow',
        '_gungame',
        '_gungame_rotated',
        '_ea_elim',
        '_turret',
        '_atls',
        '_fps_balance',
        '_fakehologram',
        '_test',
        '_placeholder',
    ];

    /**
     * Vehicle class_name suffixes (lowercase) that indicate non-player-facing variants.
     *
     * - _teach: NPC "Teach" mission variants
     * - _boarded: Boarding mission variants
     * - _tier_1/2/3: Mission progression variants (Apollo medivac/triage)
     * - _low_fuel_temporary: Temporary mission variants with reduced fuel
     * - _temp_*: Temporary dev variants
     */
    private const array EXCLUDED_VEHICLE_SUFFIXES = [
        '_teach',
        '_boarded',
        '_dunlevy',
        '_tier_1',
        '_tier_2',
        '_tier_3',
        '_low_fuel_temporary',
        '_temporary',
        '_temp_cosa',
        '_temp_cosb',
        '_temp_cosc',
        '_temp_laserrepeater',
    ];
}
