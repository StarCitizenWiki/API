<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

final class ItemSpecificationCreator
{
    public static function createSpecification(array $itemData, string $filePath): void
    {
        $filePath = storage_path(sprintf('app/%s', $filePath));

        $type = $itemData['type'];
        $subType = $itemData['sub_type'];

        switch (true) {
            // Clothing
            case stripos($type, 'Char_Clothing') !== false:
            case stripos($type, 'Char_Armor') !== false:
                Clothing::dispatch($filePath);
                break;

            // Food
            case stripos($type, 'Bottle') !== false:
            case stripos($type, 'Food') !== false:
            case stripos($type, 'Drink') !== false:
                Food::dispatch($filePath);
                break;

            // Personal Weapons
            case stripos($type, 'WeaponPersonal') !== false:
                if ($subType === 'Grenade') {
                    Grenade::dispatch($filePath);
                } else {
                    PersonalWeapon::dispatch($filePath);
                }
                break;
            case $type === 'WeaponAttachment':
                WeaponAttachment::dispatch($filePath);
                break;

            // Mining
            case stripos($type, 'WeaponMining') !== false:
                MiningLaser::dispatch($filePath);
                break;

            // Mining Modifier
            case stripos($type, 'MiningModifier') !== false:
                MiningModule::dispatch($filePath);
                break;

            // Vehicle Items
            case stripos($type, 'Battery') !== false:
            case stripos($type, 'Cooler') !== false:
            case stripos($type, 'EMP') !== false:
            case stripos($type, 'ExternalFuelTank') !== false:
            case stripos($type, 'FuelIntake') !== false:
            case stripos($type, 'FuelTank') !== false:
            case stripos($type, 'MainThruster') !== false:
            case stripos($type, 'ManneuverThruster') !== false:
            case stripos($type, 'Missile') !== false:
            case stripos($type, 'Paints') !== false:
            case stripos($type, 'PowerPlant') !== false:
            case stripos($type, 'QuantumDrive') !== false:
            case stripos($type, 'QuantumFuelTank') !== false:
            case stripos($type, 'QuantumInterdictionGenerator') !== false:
            case stripos($type, 'Radar') !== false:
            case stripos($type, 'SelfDestruct') !== false:
            case stripos($type, 'Shield') !== false:
            case stripos($type, 'WeaponDefensive') !== false:
            case stripos($type, 'WeaponGun') !== false:
            case stripos($type, 'FlightController') !== false:
            case stripos($type, 'Turret') !== false:
            case stripos($type, 'Mount') !== false:
            case stripos($type, 'Arm') !== false:
            case stripos($type, 'WheeledController') !== false:
            case in_array($type, [
                'BombLauncher',
                'MiningArm',
                'MissileLauncher',
                'ToolArm',
                'Turret',
                'TurretBase',
                'UtilityTurret',
                'WeaponMount',
            ]):
                VehicleItem::dispatch($filePath);
                break;

            default:
                break;
        }
    }
}