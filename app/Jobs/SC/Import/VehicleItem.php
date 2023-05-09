<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\ItemSpecification\Cooler;
use App\Models\SC\ItemSpecification\FlightController;
use App\Models\SC\ItemSpecification\FuelIntake;
use App\Models\SC\ItemSpecification\FuelTank;
use App\Models\SC\ItemSpecification\Missile\Missile;
use App\Models\SC\ItemSpecification\PowerPlant;
use App\Models\SC\ItemSpecification\QuantumDrive\QuantumDrive;
use App\Models\SC\ItemSpecification\SelfDestruct;
use App\Models\SC\ItemSpecification\Shield;
use App\Models\SC\ItemSpecification\Thruster;
use App\Models\SC\Vehicle\VehicleItem as VehicleItemModel;
use App\Models\SC\Vehicle\Weapon\VehicleWeapon;
use App\Services\Parser\StarCitizenUnpacked\Labels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use JsonException;

class VehicleItem implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $labels = (new Labels())->getData();

        try {
            $parser = new \App\Services\Parser\StarCitizenUnpacked\VehicleItems\VehicleItem($this->filePath, $labels);
        } catch (FileNotFoundException|JsonException $e) {
            $this->fail($e);
            return;
        }

        $item = $parser->getData();

        $shipItem = VehicleItemModel::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'grade' => $item['grade'],
            'class' => $item['class'],
            'type' => $item['type'],
        ]);

        $this->createModelSpecification($item, $shipItem);

        if (!empty($item['description'])) {
            $shipItem->translations()->updateOrCreate([
                'locale_code' => 'en_EN',
            ], [
                'translation' => $item['description'],
            ]);
        }
    }


    private function createModelSpecification(array $item, VehicleItemModel $vehicleItem): void
    {
        switch ($vehicleItem->item->type) {
            case 'FlightController':
                $this->createFlightController($item);
                break;

            case 'Cooler':
                $this->createCooler($item);
                break;

            case 'PowerPlant':
                $this->createPowerPlant($item);
                break;

            case 'Shield':
                $this->createShield($item);
                break;

            case 'QuantumDrive':
                $this->createQuantumDrive($item);
                break;

            case 'FuelTank':
            case 'ExternalFuelTank':
            case 'QuantumFuelTank':
                $this->createFuelTank($item);
                break;

            case 'FuelIntake':
                $this->createFuelIntake($item);
                break;

            case 'WeaponDefensive':
            case 'WeaponGun':
                $this->createWeapon($item);
                break;

//            case 'WeaponMining':
//                return $this->createMiningLaser($item, $shipItem);
//
//            case 'WeaponDefensive':
//                return $this->createCounterMeasure($item, $shipItem);
//
//            case 'MissileLauncher':
//                return $this->createMissileRack($item, $shipItem);

            case 'Missile':
                $this->createMissile($item);
                break;

//            case 'Ship.Turret':
//            case 'Ship.TurretBase':
//            case 'Ship.MiningArm':
//            case 'Ship.WeaponMount':
//                return $this->createTurret($item, $shipItem);
//
            case 'MainThruster':
            case 'ManneuverThruster':
                $this->createThruster($item);
                break;

            case 'SelfDestruct':
                $this->createSelfDestruct($item);
                break;
//
//            case 'Radar':
//                return $this->createRadar($item, $shipItem);
        }
    }

    private function createFlightController(array $item): void
    {
        FlightController::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'scm_speed' => $item['flight_controller']['scm_speed'] ?? null,
            'max_speed' => $item['flight_controller']['max_speed'],
            'pitch' => $item['flight_controller']['pitch'],
            'yaw' => $item['flight_controller']['yaw'],
            'roll' => $item['flight_controller']['roll'],
        ]);
    }

    private function createCooler(array $item): void
    {
        Cooler::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'cooling_rate' => $item['cooler']['cooling_rate'],
            'suppression_ir_factor' => $item['cooler']['suppression_ir_factor'],
            'suppression_heat_factor' => $item['cooler']['suppression_heat_factor'],
        ]);
    }

    private function createPowerPlant(array $item): void
    {
        PowerPlant::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'power_output' => $item['power_plant']['power_output'],
        ]);
    }

    private function createShield(array $item): void
    {
        /** @var Shield $shield */
        $shield = Shield::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'max_shield_health' => $item['shield']['max_shield_health'],
            'max_shield_regen' => $item['shield']['max_shield_regen'],
            'decay_ratio' => $item['shield']['decay_ratio'],
            'downed_regen_delay' => $item['shield']['downed_regen_delay'],
            'damage_regen_delay' => $item['shield']['damage_regen_delay'],
            'max_reallocation' => $item['shield']['max_reallocation'],
            'reallocation_rate' => $item['shield']['reallocation_rate'],
        ]);

//        foreach ($item['shield']['absorptions'] as $type => $absorption) {
//            $shield->absorptions()->updateOrCreate([
//                'ship_shield_id' => $shield->id,
//                'type' => $type
//            ], [
//                'min' => $absorption['min'] ?? 0,
//                'max' => $absorption['max'] ?? 0,
//            ]);
//        }
    }

    private function createQuantumDrive(array $item): void
    {
        /** @var QuantumDrive $drive */
        $drive = QuantumDrive::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'quantum_fuel_requirement' => $item['quantum_drive']['quantum_fuel_requirement'],
            'jump_range' => $item['quantum_drive']['jump_range'],
            'disconnect_range' => $item['quantum_drive']['disconnect_range'],
            'pre_ramp_up_thermal_energy_draw' => $item['quantum_drive']['pre_ramp_up_thermal_energy_draw'],
            'ramp_up_thermal_energy_draw' => $item['quantum_drive']['ramp_up_thermal_energy_draw'],
            'in_flight_thermal_energy_draw' => $item['quantum_drive']['in_flight_thermal_energy_draw'],
            'ramp_down_thermal_energy_draw' => $item['quantum_drive']['ramp_down_thermal_energy_draw'],
            'post_ramp_down_thermal_energy_draw' => $item['quantum_drive']['post_ramp_down_thermal_energy_draw'],
        ]);

        foreach ($item['quantum_drive']['modes'] as $type => $mode) {
            $drive->modes()->updateOrCreate([
                'type' => $type,
            ], [
                'drive_speed' => $mode['drive_speed'],
                'cooldown_time' => $mode['cooldown_time'],
                'stage_one_accel_rate' => $mode['stage_one_accel_rate'],
                'stage_two_accel_rate' => $mode['stage_two_accel_rate'],
                'engage_speed' => $mode['engage_speed'],
                'interdiction_effect_time' => $mode['interdiction_effect_time'],
                'calibration_rate' => $mode['calibration_rate'],
                'min_calibration_requirement' => $mode['min_calibration_requirement'],
                'max_calibration_requirement' => $mode['max_calibration_requirement'],
                'calibration_process_angle_limit' => $mode['calibration_process_angle_limit'],
                'calibration_warning_angle_limit' => $mode['calibration_warning_angle_limit'],
                'spool_up_time' => $mode['spool_up_time'],
            ]);
        }
    }

    private function createFuelTank(array $item): void
    {
        FuelTank::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'fill_rate' => $item['fuel_tank']['fill_rate'] ?? null,
            'drain_rate' => $item['fuel_tank']['drain_rate'] ?? null,
            'capacity' => $item['fuel_tank']['capacity'] ?? null,
        ]);
    }

    private function createFuelIntake(array $item): void
    {
        if ($item['fuel_intake'] === null) {
            return;
        }

        FuelIntake::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'fuel_push_rate' => $item['fuel_intake']['fuel_push_rate'] ?? null,
            'minimum_rate' => $item['fuel_intake']['minimum_rate'] ?? null,
        ]);
    }

    private function createWeapon(array $item): void
    {
        if (empty($item['weapon'])) {
            return;
        }

        /** @var VehicleWeapon $weapon */
        $weapon = VehicleWeapon::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'weapon_type' => Arr::get($item, 'weapon.weapon_type'),
            'weapon_class' => Arr::get($item, 'weapon.weapon_class'),
            'capacity' => Arr::get($item, 'weapon.capacity'),
        ]);

        if (!empty($item['weapon']['ammunition'])) {
            $ammunition = $weapon->ammunition()->updateOrCreate([
                'weapon_id' => $weapon->id,
            ], [
                'size' => Arr::get($item, 'weapon.ammunition.size'),
                'lifetime' => Arr::get($item, 'weapon.ammunition.lifetime'),
                'speed' => Arr::get($item, 'weapon.ammunition.speed'),
                'range' => Arr::get($item, 'weapon.ammunition.range'),
            ]);

            collect($item['weapon']['ammunition']['damages'])->each(function ($damageClass) use ($ammunition) {
                collect($damageClass)->each(function ($damage) use ($ammunition) {
                    $ammunition->damages()->updateOrCreate([
                        'type' => $damage['type'],
                        'name' => $damage['name'],
                    ], [
                        'damage' => $damage['damage'],
                    ]);
                });
            });
        }

        collect($item['weapon']['modes'])->each(function (array $mode) use ($weapon) {
            $weapon->modes()->updateOrCreate([
                'mode' => $mode['mode'],
            ], [
                'localised' => $mode['localised'] ?? null,
                'type' => $mode['type'] ?? null,
                'rounds_per_minute' => $mode['rounds_per_minute'] ?? null,
                'ammo_per_shot' => $mode['ammo_per_shot'] ?? null,
                'pellets_per_shot' => $mode['pellets_per_shot'] ?? null,
            ]);
        });

        if (!empty($item['weapon']['regen_consumption'])) {
            $weapon->regen()->updateOrCreate([
                'weapon_id' => $weapon->id,
            ], [
                'requested_regen_per_sec' => $item['weapon']['regen_consumption']['requested_regen_per_sec'],
                'requested_ammo_load' => $item['weapon']['regen_consumption']['requested_ammo_load'],
                'cooldown' => $item['weapon']['regen_consumption']['cooldown'],
                'cost_per_bullet' => $item['weapon']['regen_consumption']['cost_per_bullet'],
            ]);
        }
    }

    private function createMissileRack(array $item, VehicleItemModel $shipItem): ?Model
    {
        if ($item['missile_rack'] === null) {
            return null;
        }

        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'missile_count' => $item['missile_rack']['missile_count'] ?? null,
            'missile_size' => $item['missile_rack']['missile_size'] ?? null,
            'ship_item_id' => $shipItem->id,
        ]);
    }

    private function createMissile(array $item): void
    {
        if (!isset($item['missile']['signal_type'])) {
            return;
        }

        $missile = Missile::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'signal_type' => $item['missile']['signal_type'],
            'lock_time' => $item['missile']['lock_time'] ?? null,
        ]);

        if (isset($item['missile']['damages'])) {
            foreach ($item['missile']['damages'] as $name => $damage) {
                $missile->damages()->updateOrCreate([
                    'missile_id' => $missile->id,
                    'name' => $name,
                ], [
                    'damage' => $damage,
                ]);
            }
        }
    }

    private function createTurret(array $item, VehicleItemModel $shipItem): Model
    {

        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'min_size' => $item['turret']['min_size'] ?? null,
            'max_size' => $item['turret']['max_size'] ?? null,
            'max_mounts' => $item['turret']['max_mounts'] ?? null,
            'ship_item_id' => $shipItem->id,
        ]);
    }

    private function createThruster(array $item): void
    {
        if ($item['thruster'] === null) {
            return;
        }

        Thruster::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'thrust_capacity' => $item['thruster']['thrust_capacity'] ?? null,
            'min_health_thrust_multiplier' => $item['thruster']['min_health_thrust_multiplier'] ?? null,
            'fuel_burn_per_10k_newton' => $item['thruster']['fuel_burn_per_10k_newton'] ?? null,
            'type' => $item['thruster']['type'],
        ]);
    }

    private function createSelfDestruct(array $item): void
    {
        SelfDestruct::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'damage' => $item['self_destruct']['damage'] ?? null,
            'radius' => $item['self_destruct']['radius'] ?? null,
            'min_radius' => $item['self_destruct']['min_radius'] ?? null,
            'phys_radius' => $item['self_destruct']['phys_radius'] ?? null,
            'min_phys_radius' => $item['self_destruct']['min_phys_radius'] ?? null,
            'time' => $item['self_destruct']['time'] ?? null,
        ]);
    }

    private function createCounterMeasure(array $item, VehicleItemModel $shipItem): Model
    {
        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'initial_ammo_count' => $item['counter_measure']['initial_ammo_count'] ?? null,
            'max_ammo_count' => $item['counter_measure']['max_ammo_count'] ?? null,
            'ship_item_id' => $shipItem->id,
        ]);
    }

    private function createRadar(array $item, VehicleItemModel $shipItem): Model
    {
        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'detection_lifetime' => $item['radar']['detection_lifetime'] ?? 0,
            'altitude_ceiling' => $item['radar']['altitude_ceiling'] ?? 0,
            'enable_cross_section_occlusion' => $item['radar']['enable_cross_section_occlusion'] ?? 0,
            'ship_item_id' => $shipItem->id,
        ]);
    }

    private function createMiningLaser(array $item, VehicleItemModel $shipItem): ?Model
    {
        if (!isset($item['mining_laser'])) {
            return null;
        }

        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'modifier_resistance' => $item['mining_laser']['modifier_resistance'] ?? null,
            'modifier_instability' => $item['mining_laser']['modifier_instability'] ?? null,
            'modifier_charge_window_size' => $item['mining_laser']['modifier_charge_window_size'] ?? null,
            'modifier_charge_window_rate' => $item['mining_laser']['modifier_charge_window_rate'] ?? null,
            'modifier_shatter_damage' => $item['mining_laser']['modifier_shatter_damage'] ?? null,
            'modifier_catastrophic_window_rate' => $item['mining_laser']['modifier_catastrophic_window_rate'] ?? null,
            'ship_item_id' => $shipItem->id,
        ]);
    }

    private function createCargoGrid(array $item, VehicleItemModel $shipItem): ?Model
    {
        if (!isset($item['cargo_grid'])) {
            return null;
        }

        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'x' => $item['cargo_grid']['x'] ?? null,
            'y' => $item['cargo_grid']['y'] ?? null,
            'z' => $item['cargo_grid']['z'] ?? null,
            'ship_item_id' => $shipItem->id,
        ]);
    }

    private function createPersonalInventory(array $item, VehicleItemModel $shipItem): Model
    {
        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'scu' => $item['personal_inventory']['scu'] ?? null,
            'ship_item_id' => $shipItem->id,
        ]);
    }
}
