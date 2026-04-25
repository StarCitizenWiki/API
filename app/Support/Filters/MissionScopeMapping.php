<?php

declare(strict_types=1);

namespace App\Support\Filters;

final class MissionScopeMapping
{
    public const string BOUNTY_HUNTER = 'Bounty Hunter';

    public const string HAULING = 'Hauling';

    public const string SECURITY = 'Security';

    public const string ASSASSINATION = 'Assassination';

    public const string MINING = 'Mining';

    public const string SALVAGE = 'Salvage';

    public const string INVESTIGATION = 'Investigation';

    public const string RECOVERY = 'Recovery';

    public const string OTHER = 'Other';

    public static function scopeForRow(object $row): string
    {
        $missionType = $row->mission_type ?? null;
        $generatorClass = $row->generator_class ?? null;
        $debugName = $row->debug_name ?? null;

        if (self::rowMatchesBountyHunter($missionType, $generatorClass)) {
            return self::BOUNTY_HUNTER;
        }

        if (self::rowMatchesHauling($missionType, $generatorClass, $debugName)) {
            return self::HAULING;
        }

        if (self::rowMatchesSecurity($missionType, $generatorClass, $debugName)) {
            return self::SECURITY;
        }

        if (self::rowMatchesAssassination($missionType, $generatorClass, $debugName)) {
            return self::ASSASSINATION;
        }

        if (self::rowMatchesMining($missionType)) {
            return self::MINING;
        }

        if (self::rowMatchesSalvage($missionType, $generatorClass, $debugName)) {
            return self::SALVAGE;
        }

        if (self::rowMatchesInvestigation($missionType, $generatorClass, $debugName)) {
            return self::INVESTIGATION;
        }

        if (self::rowMatchesRecovery($generatorClass)) {
            return self::RECOVERY;
        }

        return self::OTHER;
    }

    private static function rowMatchesBountyHunter(?string $missionType, ?string $generatorClass): bool
    {
        return in_array($missionType, ['Bounty Hunter', 'PvP Missions'], true)
            || in_array($generatorClass, ['BountyHuntersGuild_KIllShip', 'NorthRock_FPSKill'], true);
    }

    private static function rowMatchesHauling(?string $missionType, ?string $generatorClass, ?string $debugName): bool
    {
        if (in_array($missionType, [
            'Hauling', 'Hauling - Interstellar', 'Hauling - Local', 'Hauling - Planetary', 'Hauling - Stellar',
            'Delivery', 'Courier',
            'Resource Drive - ArcCorp', 'Resource Drive - Crusader', 'Resource Drive - Hurston', 'Resource Drive - MicroTech',
        ], true)) {
            return true;
        }

        if (in_array($generatorClass, ['Covalex_Hauling', 'CFP_Courier', 'HH_Courier'], true)) {
            return true;
        }

        if ($generatorClass === '2025Content' && $debugName !== null) {
            return str_starts_with($debugName, 'Firesale') || str_starts_with($debugName, 'YardRush_Mining');
        }

        return false;
    }

    private static function rowMatchesSecurity(?string $missionType, ?string $generatorClass, ?string $debugName): bool
    {
        if ($missionType === 'Priority') {
            return true;
        }

        if ($generatorClass === '2025Content' && $debugName !== null) {
            return str_contains($debugName, 'DefendShip')
                || str_starts_with($debugName, 'UEETrainingExercise')
                || str_starts_with($debugName, 'UEEVisitTheHalls');
        }

        if ($generatorClass !== null && ! in_array($generatorClass, ['HeadHunters_ShipAmbush', 'EckhartSecurity_ShipAmbush'], true)) {
            return self::endsWith($generatorClass, 'DefendShip')
                || self::endsWith($generatorClass, 'Patrol')
                || self::endsWith($generatorClass, 'EscortShips')
                || self::endsWith($generatorClass, 'Ambush')
                || in_array($generatorClass, ['CDF_Generator', 'InterSec_DefendShip', 'InterSec_Patrol'], true);
        }

        return false;
    }

    private static function rowMatchesAssassination(?string $missionType, ?string $generatorClass, ?string $debugName): bool
    {
        if ($missionType === 'Mercenary') {
            return true;
        }

        if ($generatorClass === null) {
            return false;
        }

        if (str_contains($generatorClass, 'Mercenary')
            || self::endsWith($generatorClass, 'DestroyItems')
            || self::endsWith($generatorClass, 'KillNPC')
            || self::endsWith($generatorClass, 'KillShip')
            || self::endsWith($generatorClass, 'ShipWaveAttack')
            || str_contains($generatorClass, 'Hijacked')
            || str_contains($generatorClass, 'DestroyObject')
            || in_array($generatorClass, [
                'HeadHunters_ShipAmbush',
                'EckhartSecurity_ShipAmbush',
                'Ruto_Generator',
            ], true)
            || ($generatorClass === 'Unaffiliated_Generator' && ($debugName === null || ! str_starts_with($debugName, 'Manfred')))) {
            return true;
        }

        if ($generatorClass === '2025Content' && $debugName !== null && str_starts_with($debugName, 'HuntThePolaris')) {
            return true;
        }

        return false;
    }

    private static function rowMatchesMining(?string $missionType): bool
    {
        return in_array($missionType, ['Ship Mining', 'Hand Mining', 'Ground Vehicle Mining'], true);
    }

    private static function rowMatchesSalvage(?string $missionType, ?string $generatorClass, ?string $debugName): bool
    {
        if ($missionType === 'Salvage') {
            return true;
        }

        if (in_array($generatorClass, ['Adagio_Generator', 'RR_Salvage', 'TarPits_Generator'], true)) {
            return true;
        }

        if ($generatorClass === '2025Content' && $debugName !== null && str_starts_with($debugName, 'YardRush_Salvage')) {
            return true;
        }

        if ($generatorClass !== null && $debugName !== null && self::endsWith($generatorClass, '_Generator') && str_contains($debugName, 'FPSSalvage')) {
            return true;
        }

        return false;
    }

    private static function rowMatchesInvestigation(?string $missionType, ?string $generatorClass, ?string $debugName): bool
    {
        if ($missionType === 'Investigation') {
            return true;
        }

        if ($generatorClass !== null) {
            if (self::endsWith($generatorClass, 'Investigation')
                || str_contains($generatorClass, 'MissingPerson')) {
                return true;
            }

            if (in_array($generatorClass, ['HockrowAgency_MissingPerson', 'HockrowAgency_RecoverItem'], true)) {
                return true;
            }
        }

        return $generatorClass === 'Unaffiliated_Generator' && $debugName !== null && str_starts_with($debugName, 'Manfred');
    }

    private static function rowMatchesRecovery(?string $generatorClass): bool
    {
        if ($generatorClass === null) {
            return false;
        }

        if (str_contains($generatorClass, 'RecoverCargo') || str_contains($generatorClass, 'RecoverItem')) {
            return $generatorClass !== 'HockrowAgency_RecoverItem';
        }

        return false;
    }

    private static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }
}
