<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked;

use App\Console\Commands\AbstractQueueCommand;

class ImportVehicleItems extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:import-vehicle-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Vehicle Items';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        return $this->call(
    'unpacked:import-items',
            [
                '--skipVehicles',
                '--type' => 'Armor,Battery,BombLauncher,Cooler,CoolerController,EMP,ExternalFuelTank,FlightController,FuelIntake,FuelTank,MainThruster,ManneuverThruster,Missile,MissileController,MissileLauncher,Paints,PowerPlant,QuantumDrive,QuantumFuelTank,QuantumInterdictionGenerator,Radar,SelfDestruct,Shield,ShieldController,ToolArm,Turret,TurretBase,UtilityTurret,WeaponDefensive,WeaponGun,WeaponMining,WeaponMount,WheeledController'
            ]
        );
    }
}
