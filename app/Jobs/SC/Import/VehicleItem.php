<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\ItemSpecification\Armor;
use App\Models\SC\ItemSpecification\Bomb\Bomb;
use App\Models\SC\ItemSpecification\Cooler;
use App\Models\SC\ItemSpecification\Emp;
use App\Models\SC\ItemSpecification\FlightController;
use App\Models\SC\ItemSpecification\FuelIntake;
use App\Models\SC\ItemSpecification\FuelTank;
use App\Models\SC\ItemSpecification\Missile\Missile;
use App\Models\SC\ItemSpecification\PowerPlant;
use App\Models\SC\ItemSpecification\QuantumDrive\QuantumDrive;
use App\Models\SC\ItemSpecification\QuantumInterdictionGenerator;
use App\Models\SC\ItemSpecification\SalvageModifier;
use App\Models\SC\ItemSpecification\SelfDestruct;
use App\Models\SC\ItemSpecification\Shield;
use App\Models\SC\ItemSpecification\Thruster;
use App\Models\SC\ItemSpecification\TractorBeam;
use App\Models\SC\Vehicle\VehicleItem as VehicleItemModel;
use App\Models\SC\Vehicle\Weapon\VehicleWeapon;
use App\Services\Parser\SC\Labels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     */
    public function handle(): void
    {
        $labels = (new Labels())->getData();

        try {
            $parser = new \App\Services\Parser\SC\VehicleItems\VehicleItem($this->filePath, $labels);
        } catch (FileNotFoundException|JsonException $e) {
            $this->fail($e);

            return;
        }

        $item = $parser->getData();

        try {
            $itemModel = \App\Models\SC\Item\Item::where('uuid', $item['uuid'])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return;
        }

        $this->createModelSpecification($item, $itemModel);
    }

    private function createModelSpecification(array $item, \App\Models\SC\Item\Item $itemModel): void
    {
        switch ($itemModel->type) {
            case 'Armor':
                $this->createArmor($item);
                break;

            case 'Bomb':
                $this->createBomb($item);
                break;

            case 'FlightController':
                $this->createFlightController($item);
                break;

            case 'Cooler':
                $this->createCooler($item);
                break;

            case 'EMP':
                $this->createEmp($item);
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

            case 'QuantumInterdictionGenerator':
                $this->createQuantumInterdictionGenerator($item);
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

            case 'Missile':
                $this->createMissile($item);
                break;

            case 'MainThruster':
            case 'ManneuverThruster':
                $this->createThruster($item);
                break;

            case 'SelfDestruct':
                $this->createSelfDestruct($item);
                break;

            case 'TowingBeam':
            case 'TractorBeam':
                $this->createTractorBeam($item);
                break;

            case 'SalvageModifier':
                $this->createSalvageModifier($item);
                break;
        }
    }

    private function createArmor(array $item): void
    {
        Armor::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'signal_infrared' => $item['armor']['signal_infrared'] ?? null,
            'signal_electromagnetic' => $item['armor']['signal_electromagnetic'] ?? null,
            'signal_cross_section' => $item['armor']['signal_cross_section'] ?? null,
            'damage_physical' => $item['armor']['damage_physical'] ?? null,
            'damage_energy' => $item['armor']['damage_energy'] ?? null,
            'damage_distortion' => $item['armor']['damage_distortion'] ?? null,
            'damage_thermal' => $item['armor']['damage_thermal'] ?? null,
            'damage_biochemical' => $item['armor']['damage_biochemical'] ?? null,
            'damage_stun' => $item['armor']['damage_stun'] ?? null,
        ]);
    }

    private function createFlightController(array $item): void
    {
        FlightController::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'scm_speed' => $item['flight_controller']['scm_speed'] ?? null,
            'max_speed' => $item['flight_controller']['max_speed'] ?? null,
            'pitch' => $item['flight_controller']['pitch'] ?? null,
            'yaw' => $item['flight_controller']['yaw'] ?? null,
            'roll' => $item['flight_controller']['roll'] ?? null,
        ]);
    }

    private function createEmp(array $item): void
    {
        Emp::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'charge_duration' => $item['emp']['charge_duration'] ?? null,
            'emp_radius' => $item['emp']['emp_radius'] ?? null,
            'unleash_duration' => $item['emp']['unleash_duration'] ?? null,
            'cooldown_duration' => $item['emp']['cooldown_duration'] ?? null,
        ]);
    }

    private function createQuantumInterdictionGenerator(array $item): void
    {
        QuantumInterdictionGenerator::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'jammer_range' => $item['qig']['jammer_range'] ?? null,
            'interdiction_range' => $item['qig']['interdiction_range'] ?? null,
            'charge_duration' => $item['qig']['charge_duration'] ?? null,
            'discharge_duration' => $item['qig']['discharge_duration'] ?? null,
            'cooldown_duration' => $item['qig']['cooldown_duration'] ?? null,
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

        if (! empty($item['weapon']['ammunition'])) {
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

        if (! empty($item['weapon']['regen_consumption'])) {
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

    private function createMissile(array $item): void
    {
        if (! isset($item['missile']['signal_type'])) {
            return;
        }

        $lockRangeMax = $item['missile']['lock_range_max'] ?? null;
        if ($lockRangeMax !== null) {
            $lockRangeMax = max(0, $lockRangeMax);
        }
        $lockRangeMin = $item['missile']['lock_range_min'] ?? null;
        if ($lockRangeMin !== null) {
            $lockRangeMin = max(0, $lockRangeMin);
        }

        $missile = Missile::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'signal_type' => $item['missile']['signal_type'],
            'lock_time' => $item['missile']['lock_time'] ?? null,
            'lock_range_max' => $lockRangeMax,
            'lock_range_min' => $lockRangeMin,
            'lock_angle' => $item['missile']['lock_angle'] ?? null,
            'tracking_signal_min' => $item['missile']['tracking_signal_min'] ?? null,
            'speed' => $item['missile']['speed'] ?? null,
            'fuel_tank_size' => $item['missile']['fuel_tank_size'] ?? null,
            'explosion_radius_min' => $item['missile']['explosion_radius_min'] ?? null,
            'explosion_radius_max' => $item['missile']['explosion_radius_max'] ?? null,
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

    private function createBomb(array $item): void
    {
        if (! isset($item['bomb']['arm_time'])) {
            return;
        }

        $bomb = Bomb::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'arm_time' => $item['bomb']['arm_time'] ?? null,
            'ignite_time' => $item['bomb']['ignite_time'] ?? null,
            'collision_delay_time' => $item['bomb']['collision_delay_time'] ?? null,
            'explosion_safety_distance' => $item['bomb']['explosion_safety_distance'] ?? null,
            'explosion_radius_min' => $item['bomb']['explosion_radius_min'] ?? null,
            'explosion_radius_max' => $item['bomb']['explosion_radius_max'] ?? null,
        ]);

        if (isset($item['bomb']['damages'])) {
            foreach ($item['bomb']['damages'] as $name => $damage) {
                $bomb->damages()->updateOrCreate([
                    'missile_id' => $bomb->id,
                    'name' => $name,
                ], [
                    'damage' => $damage,
                ]);
            }
        }
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

    private function createTractorBeam(array $item): void
    {
        TractorBeam::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'min_force' => $item['tractor_beam']['min_force'] ?? null,
            'max_force' => $item['tractor_beam']['max_force'] ?? null,
            'min_distance' => $item['tractor_beam']['min_distance'] ?? null,
            'max_distance' => $item['tractor_beam']['max_distance'] ?? null,
            'full_strength_distance' => $item['tractor_beam']['full_strength_distance'] ?? null,
            'max_angle' => $item['tractor_beam']['max_angle'] ?? null,
            'max_volume' => $item['tractor_beam']['max_volume'] ?? null,
            'volume_force_coefficient' => $item['tractor_beam']['volume_force_coefficient'] ?? null,
            'tether_break_time' => $item['tractor_beam']['tether_break_time'] ?? null,
            'safe_range_value_factor' => $item['tractor_beam']['safe_range_value_factor'] ?? null,
        ]);
    }

    private function createSalvageModifier(array $item): void
    {
        SalvageModifier::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'salvage_speed_multiplier' => $item['salvage_modifier']['salvage_speed_multiplier'] ?? null,
            'radius_multiplier' => $item['salvage_modifier']['radius_multiplier'] ?? null,
            'extraction_efficiency' => $item['salvage_modifier']['extraction_efficiency'] ?? null,
        ]);
    }
}
