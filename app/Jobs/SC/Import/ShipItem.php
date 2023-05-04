<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\StarCitizenUnpacked\Item;
use App\Models\StarCitizenUnpacked\ShipItem\QuantumDrive\QuantumDrive;
use App\Models\StarCitizenUnpacked\ShipItem\Shield\Shield;
use App\Models\StarCitizenUnpacked\ShipItem\ShipItem as ShipItemModel;
use App\Models\StarCitizenUnpacked\ShipItem\Weapon\Weapon;
use App\Models\StarCitizenUnpacked\ShipItem\Weapon\WeaponMode;
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

class ShipItem implements ShouldQueue
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
    public function handle()
    {
        $labels = (new Labels())->getData();

        try {
            $parser = new \App\Services\Parser\StarCitizenUnpacked\ShipItems\ShipItem($this->filePath, $labels);
        } catch (FileNotFoundException | JsonException $e) {
            $this->fail($e);
            return;
        }

        $item = $parser->getData();

        $shipItem = ShipItemModel::updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'grade' => $item['grade'],
            'class' => $item['class'],
            'type' => $item['type'],
            'health' => $item['health'],
            'version' => config('api.sc_data_version'),
        ]);

        $this->createPowerDataModel($item, $shipItem);
        $this->createHeatDataModel($item, $shipItem);
        $this->createDistortionDataModel($item, $shipItem);
        $this->createDurabilityDataModel($item, $shipItem);

        $this->createModelSpecification($item, $shipItem);

        $shipItem->translations()->updateOrCreate([
            'locale_code' => 'en_EN',
        ], [
            'translation' => $item['description'] ?? '',
        ]);

        return $shipItem;
    }

    private function createPowerDataModel(array $item, ShipItemModel $model): void
    {
        if (!isset($item['power']) || $item['power'] === null) {
            return;
        }

        $model->powerData()->updateOrCreate([
            'ship_item_id' => $model->id,
        ], [
            'power_base' => $item['power']['power_base'] ?? null,
            'power_draw' => $item['power']['power_draw'] ?? null,
            'throttleable' => $item['power']['throttleable'] ?? false,
            'overclockable' => $item['power']['overclockable'] ?? false,
            'overclock_threshold_min' => $item['power']['overclock_threshold_min'] ?? null,
            'overclock_threshold_max' => $item['power']['overclock_threshold_max'] ?? null,
            'overclock_performance' => $item['power']['overclock_performance'] ?? null,
            'overpower_performance' => $item['power']['overpower_performance'] ?? null,
            'power_to_em' => $item['power']['power_to_em'] ?? null,
            'decay_rate_em' => $item['power']['decay_rate_em'] ?? null,
        ]);
    }

    private function createHeatDataModel(array $item, ShipItemModel $model): void
    {
        if (!isset($item['heat']) || $item['heat'] === null) {
            return;
        }

        $model->heatData()->updateOrCreate([
            'ship_item_id' => $model->id,
        ], [
            'temperature_to_ir' => $item['heat']['temperature_to_ir'] ?? null,
            'overpower_heat' => $item['heat']['overpower_heat'] ?? null,
            'overclock_threshold_min' => $item['heat']['overclock_threshold_min'] ?? null,
            'overclock_threshold_max' => $item['heat']['overclock_threshold_max'] ?? null,
            'thermal_energy_base' => $item['heat']['thermal_energy_base'] ?? null,
            'thermal_energy_draw' => $item['heat']['thermal_energy_draw'] ?? null,
            'thermal_conductivity' => $item['heat']['thermal_conductivity'] ?? null,
            'specific_heat_capacity' => $item['heat']['specific_heat_capacity'] ?? null,
            'mass' => $item['heat']['mass'] ?? null,
            'surface_area' => $item['heat']['surface_area'] ?? null,
            'start_cooling_temperature' => $item['heat']['start_cooling_temperature'] ?? null,
            'max_cooling_rate' => $item['heat']['max_cooling_rate'] ?? null,
            'max_temperature' => $item['heat']['max_temperature'] ?? null,
            'min_temperature' => $item['heat']['min_temperature'] ?? null,
            'overheat_temperature' => $item['heat']['overheat_temperature'] ?? null,
            'recovery_temperature' => $item['heat']['recovery_temperature'] ?? null,
            'misfire_min_temperature' => $item['heat']['misfire_min_temperature'] ?? null,
            'misfire_max_temperature' => $item['heat']['misfire_max_temperature'] ?? null,
        ]);
    }

    private function createDistortionDataModel(array $item, ShipItemModel $model): void
    {
        if (!isset($item['distortion']) || $item['distortion'] === null) {
            return;
        }

        $model->distortionData()->updateOrCreate([
            'ship_item_id' => $model->id,
        ], [
            'decay_rate' => $item['distortion']['decay_rate'] ?? null,
            'maximum' => $item['distortion']['maximum'] ?? null,
            'overload_ratio' => $item['distortion']['overload_ratio'] ?? null,
            'recovery_ratio' => $item['distortion']['recovery_ratio'] ?? null,
            'recovery_time' => $item['distortion']['recovery_time'] ?? null,
        ]);
    }

    private function createDurabilityDataModel(array $item, ShipItemModel $model): void
    {
        if (!isset($item['durability']) || $item['durability'] === null) {
            return;
        }

        $model->durabilityData()->updateOrCreate([
            'ship_item_id' => $model->id,
        ], [
            'health' => $item['durability']['health'] ?? null,
            'max_lifetime' => $item['durability']['max_lifetime'] ?? null,
        ]);
    }

    private function createModelSpecification(array $item, ShipItemModel $shipItem): ?Model
    {
        switch ($shipItem->item->type) {
            case 'CargoGrid':
            case 'Cargo':
                return $this->createCargoGrid($item, $shipItem);

            case 'Cooler':
                return $this->createCooler($item, $shipItem);

            case 'PowerPlant':
                return $this->createPowerPlant($item, $shipItem);

            case 'Shield':
                return $this->createShield($item, $shipItem);

            case 'QuantumDrive':
                return $this->createQuantumDrive($item, $shipItem);

            case 'FuelTank':
            case 'QuantumFuelTank':
                return $this->createFuelTank($item, $shipItem);

            case 'FuelIntake':
                return $this->createFuelIntake($item, $shipItem);

            case 'WeaponGun':
                return $this->createWeapon($item, $shipItem);

            case 'WeaponMining':
                return $this->createMiningLaser($item, $shipItem);

            case 'WeaponDefensive':
                return $this->createCounterMeasure($item, $shipItem);

            case 'MissileLauncher':
                return $this->createMissileRack($item, $shipItem);

            case 'Missile':
                return $this->createMissile($item, $shipItem);

//            case 'Ship.Turret':
//            case 'Ship.TurretBase':
//            case 'Ship.MiningArm':
//            case 'Ship.WeaponMount':
//                return $this->createTurret($item, $shipItem);

            case 'MainThruster':
            case 'ManneuverThruster':
                return $this->createThruster($item, $shipItem);

            case 'SelfDestruct':
                return $this->createSelfDestruct($item, $shipItem);

            case 'Radar':
                return $this->createRadar($item, $shipItem);

            default:
                return null;
        }
    }

    private function createCooler(array $item, ShipItemModel $shipItem): Model
    {
        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'cooling_rate' => $item['cooler']['cooling_rate'],
            'suppression_ir_factor' => $item['cooler']['suppression_ir_factor'],
            'suppression_heat_factor' => $item['cooler']['suppression_heat_factor'],
            'ship_item_id' => $shipItem->id,
        ]);
    }

    private function createPowerPlant(array $item, ShipItemModel $shipItem): Model
    {
        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'power_output' => $item['power_plant']['power_output'],
            'ship_item_id' => $shipItem->id,
        ]);
    }

    private function createShield(array $item, ShipItemModel $shipItem): Model
    {
        /** @var Shield $shield */
        $shield = $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'max_shield_health' => $item['shield']['max_shield_health'],
            'max_shield_regen' => $item['shield']['max_shield_regen'],
            'decay_ratio' => $item['shield']['decay_ratio'],
            'downed_regen_delay' => $item['shield']['downed_regen_delay'],
            'damage_regen_delay' => $item['shield']['damage_regen_delay'],
            'max_reallocation' => $item['shield']['max_reallocation'],
            'reallocation_rate' => $item['shield']['reallocation_rate'],
            'ship_item_id' => $shipItem->id,
        ]);

        foreach ($item['shield']['absorptions'] as $type => $absorption) {
            $shield->absorptions()->updateOrCreate([
                'ship_shield_id' => $shield->id,
                'type' => $type
            ], [
                'min' => $absorption['min'] ?? 0,
                'max' => $absorption['max'] ?? 0,
            ]);
        }

        return $shield;
    }

    private function createQuantumDrive(array $item, ShipItemModel $shipItem): Model
    {
        /** @var QuantumDrive $drive */
        $drive = $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'quantum_fuel_requirement' => $item['quantum_drive']['quantum_fuel_requirement'],
            'jump_range' => $item['quantum_drive']['jump_range'],
            'disconnect_range' => $item['quantum_drive']['disconnect_range'],
            'pre_ramp_up_thermal_energy_draw' => $item['quantum_drive']['pre_ramp_up_thermal_energy_draw'],
            'ramp_up_thermal_energy_draw' => $item['quantum_drive']['ramp_up_thermal_energy_draw'],
            'in_flight_thermal_energy_draw' => $item['quantum_drive']['in_flight_thermal_energy_draw'],
            'ramp_down_thermal_energy_draw' => $item['quantum_drive']['ramp_down_thermal_energy_draw'],
            'post_ramp_down_thermal_energy_draw' => $item['quantum_drive']['post_ramp_down_thermal_energy_draw'],
            'ship_item_id' => $shipItem->id,
        ]);

        foreach ($item['quantum_drive']['modes'] as $type => $mode) {
            $drive->modes()->updateOrCreate([
                'type' => $type,
            ], [
                'drive_speed' => $mode['drive_speed'],
                'cooldown_time' => $mode['cooldown_time'] ?? null,
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

        return $drive;
    }

    private function createFuelTank(array $item, ShipItemModel $shipItem): Model
    {
        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'fill_rate' => $item['fuel_tank']['fill_rate'] ?? null,
            'drain_rate' => $item['fuel_tank']['drain_rate'] ?? null,
            'capacity' => $item['fuel_tank']['capacity'] ?? null,
            'ship_item_id' => $shipItem->id,
        ]);
    }

    private function createFuelIntake(array $item, ShipItemModel $shipItem): ?Model
    {
        if ($item['fuel_intake'] === null) {
            return null;
        }

        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'fuel_push_rate' => $item['fuel_intake']['fuel_push_rate'] ?? null,
            'minimum_rate' => $item['fuel_intake']['minimum_rate'] ?? null,
            'ship_item_id' => $shipItem->id,
        ]);
    }

    private function createWeapon(array $item, ShipItemModel $shipItem): ?Model
    {
        if (!isset($item['weapon']) && $shipItem->item->type !== 'Tractor Beam') {
            return null;
        }


        /** @var Weapon $weapon */
        $weapon = $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'speed' => Arr::get($item, 'weapon.ammunition.speed'),
            'range' => Arr::get($item, 'weapon.ammunition.range'),
            'size' => Arr::get($item, 'weapon.size'),
            'capacity' => Arr::get($item, 'weapon.capacity'),

            'ship_item_id' => $shipItem->id,
        ]);

        foreach (($item['weapon']['ammunition']['damages'] ?? []) as $type => $damage) {
            if (empty($damage)) {
                continue;
            }

            foreach ($damage as $value) {
                $weapon->damages()->updateOrCreate([
                    'ship_weapon_id' => $weapon->id,
                    'type' => $value['type'],
                    'name' => $value['name'],
                ], [
                    'damage' => $value['damage'],
                ]);
            }
        }

        foreach (($item['weapon']['modes'] ?? []) as $mode) {
            /** @var WeaponMode $mode */
            $weapon->modes()->updateOrCreate([
                'mode' => $mode['mode'],
            ], [
                'localised' => $mode['localised'] ?? null,
                'type' => $mode['type'] ?? null,
                'rounds_per_minute' => $mode['rounds_per_minute'] ?? null,
                'ammo_per_shot' => $mode['ammo_per_shot'] ?? null,
                'pellets_per_shot' => $mode['pellets_per_shot'] ?? null,
            ]);
        }

        if (!empty($item['weapon']['regen_consumption'])) {
            $weapon->regen()->updateOrCreate([
                'ship_weapon_id' => $weapon->id,
            ], [
                'requested_regen_per_sec' => $item['weapon']['regen_consumption']['requested_regen_per_sec'],
                'requested_ammo_load' => $item['weapon']['regen_consumption']['requested_ammo_load'],
                'cooldown' => $item['weapon']['regen_consumption']['cooldown'],
                'cost_per_bullet' => $item['weapon']['regen_consumption']['cost_per_bullet'],
            ]);
        }

        return $weapon;
    }

    private function createMissileRack(array $item, ShipItemModel $shipItem): ?Model
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

    private function createMissile(array $item, ShipItemModel $shipItem): ?Model
    {
        if (!isset($item['missile']['signal_type'])) {
            return null;
        }

        $missile = $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'signal_type' => $item['missile']['signal_type'],
            'lock_time' => $item['missile']['lock_time'] ?? null,
            'ship_item_id' => $shipItem->id,
        ]);

        if (isset($item['missile']['damages'])) {
            foreach ($item['missile']['damages'] as $name => $damage) {
                $missile->damages()->updateOrCreate([
                    'ship_missile_id' => $missile->id,
                    'name' => $name,
                ], [
                    'damage' => $damage,
                ]);
            }
        }

        return $missile;
    }

    private function createTurret(array $item, ShipItemModel $shipItem): Model
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

    private function createThruster(array $item, ShipItemModel $shipItem): ?Model
    {
        if ($item['thruster'] === null) {
            return null;
        }

        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'thrust_capacity' => $item['thruster']['thrust_capacity'] ?? null,
            'min_health_thrust_multiplier' => $item['thruster']['min_health_thrust_multiplier'] ?? null,
            'fuel_burn_per_10k_newton' => $item['thruster']['fuel_burn_per_10k_newton'] ?? null,
            'type' => $item['thruster']['type'],
            'ship_item_id' => $shipItem->id,
        ]);
    }

    private function createSelfDestruct(array $item, ShipItemModel $shipItem): Model
    {
        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'damage' => $item['self_destruct']['damage'] ?? null,
            'radius' => $item['self_destruct']['radius'] ?? null,
            'min_radius' => $item['self_destruct']['min_radius'] ?? null,
            'phys_radius' => $item['self_destruct']['phys_radius'] ?? null,
            'min_phys_radius' => $item['self_destruct']['min_phys_radius'] ?? null,
            'time' => $item['self_destruct']['time'] ?? null,
            'ship_item_id' => $shipItem->id,
        ]);
    }

    private function createCounterMeasure(array $item, ShipItemModel $shipItem): Model
    {
        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'initial_ammo_count' => $item['counter_measure']['initial_ammo_count'] ?? null,
            'max_ammo_count' => $item['counter_measure']['max_ammo_count'] ?? null,
            'ship_item_id' => $shipItem->id,
        ]);
    }

    private function createRadar(array $item, ShipItemModel $shipItem): Model
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

    private function createMiningLaser(array $item, ShipItemModel $shipItem): ?Model
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

    private function createCargoGrid(array $item, ShipItemModel $shipItem): ?Model
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

    private function createPersonalInventory(array $item, ShipItemModel $shipItem): Model
    {
        return $shipItem->specification()->updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'scu' => $item['personal_inventory']['scu'] ?? null,
            'ship_item_id' => $shipItem->id,
        ]);
    }
}
