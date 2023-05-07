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


            case $type === 'WeaponAttachment':
                WeaponAttachment::dispatch($filePath);
                break;

            // Personal Weapons
            case stripos($type, 'WeaponPersonal') !== false:
                if ($subType === 'Grenade') {
                    Grenade::dispatch($filePath);
                } else {
                    PersonalWeapon::dispatch($filePath);
                }
                break;
//
//            // Personal Weapon Attachments
//            case stripos($type, 'WeaponAttachment') !== false && !stripos($itemData['tags'], 'uneditable') === false:
//                WeaponAttachment::dispatch($filePath);
//                break;
//
//            // TODO: These are only Quest Hacking Chips?
//            case stripos($type, 'FPS_Consumable') !== false:
//                break;
//
//

//
//            /// Vehicles Stuff
//            case stripos($type, 'GroundVehicle') !== false:
//            case stripos($type, 'Vehicle') !== false:
//                break;
//
            // Vehicle Items
//            case stripos($type, 'Battery') !== false:
//            case stripos($type, 'BombLauncher') !== false:
//            case stripos($type, 'Cargo') !== false:
//            case stripos($type, 'CargoGrid') !== false:
            case stripos($type, 'Cooler') !== false:
//            case stripos($type, 'EMP') !== false:
            case stripos($type, 'ExternalFuelTank') !== false:
            case stripos($type, 'FuelIntake') !== false:
            case stripos($type, 'FuelTank') !== false:
            case stripos($type, 'MainThruster') !== false:
            case stripos($type, 'ManneuverThruster') !== false:
//            case stripos($type, 'Missile') !== false:
//            case stripos($type, 'MissileLauncher') !== false:
//            case stripos($type, 'Paints') !== false:
//            case stripos($type, 'Ping') !== false:
            case stripos($type, 'PowerPlant') !== false:
            case stripos($type, 'QuantumDrive') !== false:
            case stripos($type, 'QuantumFuelTank') !== false:
//            case stripos($type, 'QuantumInterdictionGenerator') !== false:
//            case stripos($type, 'Radar') !== false:
//            case stripos($type, 'Scanner') !== false:
            case stripos($type, 'SelfDestruct') !== false:
            case stripos($type, 'Shield') !== false:
//            case stripos($type, 'ToolArm') !== false:
//            case stripos($type, 'Transponder') !== false:
//            case stripos($type, 'Turret') !== false:
//            case stripos($type, 'TurretBase') !== false:
//            case stripos($type, 'UtilityTurret') !== false:
//            case stripos($type, 'WeaponDefensive') !== false:
//            case stripos($type, 'WeaponGun') !== false:
//            case stripos($type, 'WeaponMining') !== false:
//            case stripos($type, 'WeaponMount') !== false:
            case stripos($type, 'FlightController') !== false:
                VehicleItem::dispatch($filePath);
                break;
//
//            /// Vehicle Weapons
//
//
//
//            // Mining Modifier
//            case stripos($type, 'MiningModifier') !== false:
//                break;
//


            default:
                break;
        }
    }

}