<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use App\Http\Resources\Game\Item\ItemInventoryResource;
use App\Models\Game\ItemData;
use Illuminate\Support\Arr;

final class SpecificationRegistry
{
    /** @var array<int, array{predicate: callable, handler: callable, deprecated: array}> */
    private array $handlers = [];

    private static ?self $instance = null;

    public static function make(): self
    {
        return self::$instance ??= self::create();
    }

    /**
     * Register a single-key specification handler.
     *
     * @param  callable(ItemData): bool  $predicate
     * @param  class-string  $resourceClass
     */
    public function register(
        callable $predicate,
        string $specKey,
        string $resourceClass,
        array $deprecated = [],
    ): void {
        $this->handlers[] = [
            'predicate' => $predicate,
            'handler' => fn (ItemData $d): array => [$specKey => static fn (): object => new $resourceClass($d)],
            'deprecated' => $deprecated,
        ];
    }

    /**
     * Register a multi-key specification handler (e.g. Grenade -> grenade + personal_weapon).
     *
     * @param  callable(ItemData): bool  $predicate
     * @param  array<string, class-string>  $specs  specKey => resourceClass
     */
    public function registerMulti(
        callable $predicate,
        array $specs,
        array $deprecated = [],
    ): void {
        $this->handlers[] = [
            'predicate' => $predicate,
            'handler' => function (ItemData $d) use ($specs): array {
                $result = [];
                foreach ($specs as $key => $class) {
                    $result[$key] = static fn (): object => new $class($d);
                }

                return $result;
            },
            'deprecated' => $deprecated,
        ];
    }

    /**
     * Register a custom specification handler that produces dynamic keys.
     *
     * @param  callable(ItemData): bool  $predicate
     * @param  callable(ItemData): array<string, callable>  $handler  Returns specKey => callable pairs
     */
    public function registerCustom(
        callable $predicate,
        callable $handler,
        array $deprecated = [],
    ): void {
        $this->handlers[] = [
            'predicate' => $predicate,
            'handler' => $handler,
            'deprecated' => $deprecated,
        ];
    }

    /**
     * Resolve all matching specifications for the given item data.
     *
     * @return array{has_match: bool, specifications: array<string, callable>, deprecated: array}
     */
    public function resolve(ItemData $itemData): array
    {
        $specifications = [];
        $deprecated = [];

        foreach ($this->handlers as $entry) {
            if (! ($entry['predicate'])($itemData)) {
                continue;
            }

            foreach (($entry['handler'])($itemData) as $key => $value) {
                $specifications[$key] = $value;
            }

            if ($entry['deprecated'] !== []) {
                $deprecated[] = $entry['deprecated'];
            }
        }

        return [
            'has_match' => $specifications !== [],
            'specifications' => $specifications,
            'deprecated' => $deprecated,
        ];
    }

    private static function create(): self
    {
        $registry = new self;

        // 1. FPS Clothing
        $registry->register(
            predicate: fn (ItemData $d): bool => str_starts_with($d->classification ?? '', 'FPS.Clothing.'),
            specKey: 'clothing',
            resourceClass: ClothingResource::class,
            deprecated: [
                'clothing' => [
                    'clothing_type' => 'Use type instead.',
                    'temp_resistance_min' => 'Use temperature_resistance from root.',
                    'temp_resistance_max' => 'Use temperature_resistance from root.',
                ],
            ],
        );

        // Also emit suit_armor for all clothing items
        $registry->register(
            predicate: fn (ItemData $d): bool => str_starts_with($d->classification ?? '', 'FPS.Clothing.'),
            specKey: 'suit_armor',
            resourceClass: SuitArmorResource::class,
        );

        // 2. FPS Armor (multi-key: clothing + suit_armor)
        $registry->registerMulti(
            predicate: fn (ItemData $d): bool => str_starts_with($d->classification ?? '', 'FPS.Armor.'),
            specs: ['clothing' => SuitArmorResource::class, 'suit_armor' => SuitArmorResource::class],
        );

        // 3. Ship Armor
        $registry->register(
            predicate: fn (ItemData $d): bool => str_starts_with($d->classification ?? '', 'Ship.Armor.'),
            specKey: 'armor',
            resourceClass: ArmorResource::class,
            deprecated: [
                'armor' => [
                    'signal_infrared' => 'Use signal_multiplier.infrared instead.',
                    'signal_electromagnetic' => 'Use signal_multiplier.electromagnetic instead.',
                    'signal_cross_section' => 'Use signal_multiplier.cross_section instead.',
                    'damage_physical' => 'Use damage_multiplier.physical instead.',
                    'damage_energy' => 'Use damage_multiplier.energy instead.',
                    'damage_distortion' => 'Use damage_multiplier.distortion instead.',
                    'damage_thermal' => 'Use damage_multiplier.thermal instead.',
                    'damage_biochemical' => 'Use damage_multiplier.biochemical instead.',
                    'damage_stun' => 'Use damage_multiplier.stun instead.',
                ],
            ],
        );

        // 4. Thrusters
        $registry->register(
            predicate: fn (ItemData $d): bool => str_starts_with($d->classification ?? '', 'Ship.MainThruster')
                || str_starts_with($d->classification ?? '', 'Ship.ManneuverThruster'),
            specKey: 'thruster',
            resourceClass: ThrusterResource::class,
        );

        // 5. Flight Controller
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'FlightController',
            specKey: 'flight_controller',
            resourceClass: FlightControllerResource::class,
        );

        // 6. Fuel Tanks
        $registry->register(
            predicate: fn (ItemData $d): bool => in_array($d->type, ['FuelTank', 'QuantumFuelTank', 'ExternalFuelTank'], true),
            specKey: 'fuel_tank',
            resourceClass: FuelTankResource::class,
        );

        // 7. Hacking Chip
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->sub_type === 'Hacking',
            specKey: 'hacking_chip',
            resourceClass: HackingChipResource::class,
        );

        // 8. Mining Laser
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'WeaponMining',
            specKey: 'mining_laser',
            resourceClass: MiningLaserResource::class,
        );

        // 9. Mining Module / Modifier (type-based)
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'MiningModifier',
            specKey: 'mining_module',
            resourceClass: MiningModuleResource::class,
            deprecated: ['mining_module' => 'Use mining_modifier instead.'],
        );

        // 10. Mining Gadget
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'Gadget' && Arr::has($d->data, 'stdItem.MiningModule'),
            specKey: 'mining_gadget',
            resourceClass: MiningModuleResource::class,
        );

        // 11. Quantum Drive
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'QuantumDrive',
            specKey: 'quantum_drive',
            resourceClass: QuantumDriveResource::class,
        );

        // 12. Quantum Interdiction Generator
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'QuantumInterdictionGenerator',
            specKey: 'quantum_interdiction_generator',
            resourceClass: QuantumInterdictionGeneratorResource::class,
        );

        // 13. Self Destruct
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'SelfDestruct',
            specKey: 'self_destruct',
            resourceClass: SelfDestructResource::class,
        );

        // 14. Bombs
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'Bomb',
            specKey: 'bomb',
            resourceClass: BombResource::class,
            deprecated: [
                'bomb' => [
                    'explosion_safety_distance' => 'Use explosion.safety_distance instead.',
                    'explosion_radius_min' => 'Use explosion.radius_min instead.',
                    'explosion_radius_max' => 'Use explosion.radius_max instead.',
                    'damage' => 'Use damage_total instead.',
                    'damages' => 'Use damage_map instead.',
                ],
            ],
        );

        // 15. Missiles / Torpedoes
        $registry->register(
            predicate: fn (ItemData $d): bool => in_array($d->type, ['Torpedo', 'Missile'], true),
            specKey: 'missile',
            resourceClass: MissileResource::class,
        );

        // 16. EMP
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'EMP',
            specKey: 'emp',
            resourceClass: EmpResource::class,
        );

        // 17. Cooler
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'Cooler',
            specKey: 'cooler',
            resourceClass: CoolerResource::class,
        );

        // 18. Turret
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'Turret',
            specKey: 'turret',
            resourceClass: TurretResource::class,
        );

        // 19. Tractor / Towing Beam
        // S3 tractor/towing beams are typed as SalvageHead, so also detect by presence
        // of the TractorBeam data block.
        $registry->register(
            predicate: fn (ItemData $d): bool => in_array($d->type, ['TractorBeam', 'TowingBeam'], true)
                || Arr::has($d->data, 'stdItem.TractorBeam'),
            specKey: 'tractor_beam',
            resourceClass: TractorBeamResource::class,
        );

        // 20. Shield
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'Shield',
            specKey: 'shield',
            resourceClass: ShieldResource::class,
        );

        // 21. Shield Controller
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'ShieldController',
            specKey: 'shield_controller',
            resourceClass: ShieldControllerResource::class,
        );

        // 22. Jump Drive
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'JumpDrive',
            specKey: 'jump_drive',
            resourceClass: JumpDriveResource::class,
        );

        // 23. Grenade (must be before general WeaponPersonal) - multi-key
        $registry->registerMulti(
            predicate: fn (ItemData $d): bool => $d->type === 'WeaponPersonal' && $d->sub_type === 'Grenade',
            specs: ['grenade' => GrenadeResource::class, 'personal_weapon' => PersonalWeaponResource::class],
        );

        // 24. Knife / Melee Weapon (must be before general WeaponPersonal) - multi-key
        $registry->registerMulti(
            predicate: fn (ItemData $d): bool => $d->type === 'WeaponPersonal' && $d->sub_type === 'Knife',
            specs: ['melee_weapon' => MeleeWeaponResource::class, 'knife' => MeleeWeaponResource::class],
        );

        // 25. Personal Weapon (general - after specific grenade / knife checks)
        $registry->register(
            predicate: fn (ItemData $d): bool => ($d->type === 'WeaponPersonal' || str_starts_with($d->classification ?? '', 'FPS.Weapon.'))
                && ($d->type !== 'WeaponPersonal' || ! in_array($d->sub_type, ['Grenade', 'Knife'], true)),
            specKey: 'personal_weapon',
            resourceClass: PersonalWeaponResource::class,
            deprecated: [
                'personal_weapon' => [
                    'rof' => 'Use rpm instead',
                    'effective_range' => 'Use range instead',
                    'magazine_size' => 'Use capacity instead',
                    'damage_per_shot' => 'Use damage.alpha_total instead',
                ],
            ],
        );

        // 26. Salvage Modifier -> weapon_modifier.salvage
        $registry->register(
            predicate: fn (ItemData $d): bool => Arr::has($d->data, 'stdItem.SalvageModifier') && ! Arr::has($d->data, 'stdItem.WeaponModifier'),
            specKey: 'weapon_modifier',
            resourceClass: SalvageModifierResource::class,
            deprecated: [
                'salvage_modifier' => [
                    'salvage_speed_multiplier' => 'Use weapon_modifier.salvage.salvage_speed_multiplier instead.',
                    'radius_multiplier' => 'Use weapon_modifier.salvage.radius_multiplier instead.',
                    'extraction_efficiency' => 'Use weapon_modifier.salvage.extraction_efficiency instead.',
                ],
            ],
        );

        // 27. Weapon Modifier (merges SalvageModifier into weapon_modifier.salvage when both are present)
        $registry->registerCustom(
            predicate: fn (ItemData $d): bool => Arr::has($d->data, 'stdItem.WeaponModifier'),
            handler: function (ItemData $d): array {
                $result = new WeaponModifierResource($d)->resolve();

                if (Arr::has($d->data, 'stdItem.SalvageModifier')) {
                    $salvage = new SalvageModifierResource($d)->resolve()['salvage'] ?? [];

                    if ($salvage !== []) {
                        $result['salvage'] = $salvage;
                    }
                }

                return ['weapon_modifier' => static fn () => $result];
            },
            deprecated: [
                'weapon_modifier' => [
                    'fire_rate_multiplier' => 'use `base.fire_rate_multiplier` instead.',
                    'damage_multiplier' => 'use `base.damage_multiplier` instead.',
                    'damage_over_time_multiplier' => 'use `base.damage_over_time_multiplier` instead.',
                    'projectile_speed_multiplier' => 'use `base.projectile_speed_multiplier` instead.',
                    'ammo_cost_multiplier' => 'use `base.ammo_cost_multiplier` instead.',
                    'heat_generation_multiplier' => 'use `base.heat_generation_multiplier` instead.',
                    'sound_radius_multiplier' => 'use `base.sound_radius_multiplier` instead.',
                    'charge_time_multiplier' => 'use `base.charge_time_multiplier` instead.',
                ],
            ],
        );

        // 28. Weapon Attachment (dynamic keys from resolved resource)
        $registry->registerCustom(
            predicate: fn (ItemData $d): bool => $d->type === 'WeaponAttachment' || Arr::has($d->data, 'stdItem.WeaponAttachment'),
            handler: function (ItemData $d): array {
                $resolved = new WeaponAttachmentResource($d)->resolve();

                return array_map(static function ($data) {
                    return static fn () => $data;
                }, $resolved);
            },
        );

        // 29. Food / Drink
        $registry->register(
            predicate: fn (ItemData $d): bool => in_array($d->type, ['Food', 'Bottle', 'Drink'], true) || Arr::has($d->data, 'stdItem.Food'),
            specKey: 'food',
            resourceClass: FoodResource::class,
        );

        // 30. Medicine
        $registry->register(
            predicate: fn (ItemData $d): bool => Arr::has($d->data, 'stdItem.Medical'),
            specKey: 'medical',
            resourceClass: MedicineResource::class,
        );

        // 31. Countermeasures
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'WeaponDefensive' || str_contains($d->classification ?? '', 'WeaponDefensive'),
            specKey: 'counter_measure',
            resourceClass: CounterMeasureResource::class,
        );

        // 32. Missile Rack
        $registry->register(
            predicate: fn (ItemData $d): bool => ($d->type === 'MissileLauncher' && $d->sub_type === 'MissileRack')
                || str_contains($d->classification ?? '', 'MissileRack'),
            specKey: 'missile_rack',
            resourceClass: MissileRackResource::class,
        );

        // 33. Fuel Intake
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'FuelIntake' || Arr::has($d->data, 'stdItem.FuelIntake'),
            specKey: 'fuel_intake',
            resourceClass: FuelIntakeResource::class,
        );

        // 34. Power Plant
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'PowerPlant' || str_contains($d->classification ?? '', 'PowerPlant'),
            specKey: 'power_plant',
            resourceClass: PowerPlantResource::class,
        );

        // 35. Radar
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'Radar' || str_contains($d->classification ?? '', 'Radar'),
            specKey: 'radar',
            resourceClass: RadarResource::class,
        );

        // 36. Cargo Grid
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type === 'CargoGrid' || str_contains($d->classification ?? '', 'CargoGrid'),
            specKey: 'cargo_grid',
            resourceClass: ItemInventoryResource::class,
        );

        // 37. Vehicle Weapon (excludes WeaponPersonal and FPS.Weapon)
        $registry->register(
            predicate: fn (ItemData $d): bool => $d->type !== 'WeaponPersonal'
                && ! str_starts_with($d->classification ?? '', 'FPS.Weapon.')
                && Arr::has($d->data, 'stdItem.Weapon'),
            specKey: 'vehicle_weapon',
            resourceClass: VehicleWeaponResource::class,
        );

        // 38. Seat
        $registry->register(
            predicate: fn (ItemData $d): bool => Arr::has($d->data, 'stdItem.Seat'),
            specKey: 'seat',
            resourceClass: SeatResource::class,
        );

        // 39. Ammunition
        $registry->register(
            predicate: fn (ItemData $d): bool => Arr::has($d->data, 'stdItem.Ammunition'),
            specKey: 'ammunition',
            resourceClass: AmmunitionResource::class,
            deprecated: [
                'ammunition' => [
                    'impact_damage' => 'Use impact_damage_map instead.',
                    'detonation_damage' => 'Use detonation_damage_map instead.',
                ],
            ],
        );

        // 40. Mining Modifier (from stdItem - separate from type-based mining_module)
        $registry->register(
            predicate: fn (ItemData $d): bool => Arr::has($d->data, 'stdItem.MiningModule'),
            specKey: 'mining_modifier',
            resourceClass: MiningModifierResource::class,
        );

        // 41. Weapon Rack
        $registry->register(
            predicate: fn (ItemData $d): bool => str_starts_with($d->class_name, 'Weapon_Rack_'),
            specKey: 'weapon_rack',
            resourceClass: WeaponRackResource::class,
        );

        return $registry;
    }
}
