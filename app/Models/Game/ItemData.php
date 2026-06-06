<?php

declare(strict_types=1);

namespace App\Models\Game;

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\Mission\MissionData;
use App\Support\Filters\ItemFilterLabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ItemData extends Model
{
    use HasFactory;
    use HasGameVersion;

    protected $table = 'game_item_data';

    protected $perPage = 50;

    protected $fillable = [
        'item_id',
        'game_version_id',
        'name',
        'class_name',
        'type',
        'sub_type',
        'classification',
        'size',
        'grade',
        'class',
        'manufacturer_id',
        'base_id',
        'data',
        'rarity',
        'is_player_relevant',
        'uex_prices',
        'is_bespoke',
        'bespoke_vehicle_tags',
    ];

    protected $casts = [
        'size' => 'integer',
        'grade' => 'integer',
        'is_player_relevant' => 'boolean',
        'is_bespoke' => 'boolean',
        'data' => 'array',
        'uex_prices' => 'array',
        'bespoke_vehicle_tags' => 'array',
    ];

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->forCategory($category);
    }

    public function getDescriptionDatum(string $name)
    {
        if ($this->relationLoaded('descriptionData')) {
            return $this->descriptionData
                ->firstWhere('name', $name)?->value;
        }

        return $this->descriptionData()
            ->where('name', $name)
            ->first()?->value;
    }

    public function getDescriptionTypeAttribute()
    {
        return $this->getDescriptionDatum('Type');
    }

    public function getDescriptionManufacturerAttribute()
    {
        return $this->getDescriptionDatum('Manufacturer');
    }

    public function getTypeLabelAttribute(): ?string
    {
        return ItemFilterLabel::resolveType($this->type, null);
    }

    public function getSubTypeLabelAttribute(): ?string
    {
        return ItemFilterLabel::resolveSubType($this->sub_type, null);
    }

    public function getClassificationLabelAttribute(): ?string
    {
        return ItemFilterLabel::resolveClassification($this->classification, null);
    }

    /**
     * @return array<int, array{uuid: string, name: string}>
     */
    public function getBlueprintAttribute(): array
    {
        if (! $this->relationLoaded('craftingBlueprints')) {
            $this->load(['craftingBlueprints' => fn ($q) => $q
                ->where('game_blueprint_data.game_version_id', $this->game_version_id)
                ->orderByDesc('game_blueprint_data.is_available_by_default')
                ->orderBy('game_blueprint_data.output_name')
                ->orderBy('game_blueprint_data.key')
                ->orderBy('game_blueprint_data.blueprint_id')
                ->with('blueprint:id,uuid'),
            ]);
        }

        /** @var Collection<int, BlueprintData> $craftingBlueprints */
        $craftingBlueprints = $this->craftingBlueprints
            ->filter(fn (BlueprintData $blueprintData): bool => $blueprintData->blueprint !== null)
            ->values();

        /** @var array<string, bool> $ambiguousOutputNames */
        $ambiguousOutputNames = array_fill_keys(
            $craftingBlueprints
                ->map(
                    fn (BlueprintData $blueprintData): ?string => $this->normalizeCraftingBlueprintLabel($blueprintData->output_name)
                )
                ->filter(fn (?string $outputName): bool => $outputName !== null)
                ->countBy()
                ->filter(fn (int $count): bool => $count > 1)
                ->keys()
                ->all(),
            true
        );

        return $craftingBlueprints
            ->map(
                fn (BlueprintData $blueprintData): array => $this->craftingBlueprintSummary($blueprintData, $ambiguousOutputNames)
            )
            ->all();
    }

    public function getIsCraftableAttribute(): bool
    {
        return $this->blueprint !== [];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function craftingBlueprints(): HasManyThrough
    {
        return $this->hasManyThrough(
            BlueprintData::class,
            Item::class,
            'id',
            'output_item_uuid',
            'item_id',
            'uuid',
        );
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function descriptionData(): HasMany
    {
        return $this->hasMany(ItemDescriptionData::class, 'item_id', 'item_id')->orderBy('name');
    }

    public function baseVariant(): BelongsTo
    {
        return $this->belongsTo(self::class, 'base_id', 'id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(self::class, 'base_id', 'id')->orderBy('name');
    }

    public function entityTags(): BelongsToMany
    {
        return $this->belongsToMany(
            EntityTag::class,
            'game_item_data_entity_tag',
            'item_data_id',
            'entity_tag_id'
        );
    }

    public function commodities(): BelongsToMany
    {
        return $this->belongsToMany(
            Commodity::class,
            'game_item_data_commodity',
            'item_data_id',
            'commodity_id'
        );
    }

    public function variantGroupItem(): HasOne
    {
        return $this->hasOne(VariantGroupItem::class, 'item_data_id');
    }

    public function setItems(): BelongsToMany
    {
        return $this->belongsToMany(
            __CLASS__,
            'game_item_set_items',
            'item_data_id',
            'set_item_data_id',
        );
    }

    public function missions(): BelongsToMany
    {
        return $this->belongsToMany(
            MissionData::class,
            'game_mission_data_item',
            'item_data_id',
            'mission_data_id',
        );
    }

    public function installedOnVehicles(): BelongsToMany
    {
        return $this->belongsToMany(
            VehicleData::class,
            'game_item_data_vehicle_data',
            'item_data_id',
            'vehicle_data_id'
        );
    }

    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return match ($category) {
            'food' => $query->food(),
            'medical' => $query->medical(),
            'weapon-attachments' => $query->weaponAttachments(),
            'weapons' => $query->personalWeapons(),
            'mining-modifiers' => $query->miningModifiers(),
            'clothes' => $query->clothes(),
            'fps-armor' => $query->armor(),
            'fps-items' => $query->fpsItems(),
            'vehicle-weapons' => $query->vehicleWeapons(),
            'vehicle-items' => $query->vehicleItems(),
            'vehicle-flair-items' => $query->vehicleFlairItems(),
            'vehicle-components' => $query->vehicleComponents(),
            default => $query->whereDoesntHave('item.vehicle'),
        };
    }

    public function scopeFood(Builder $query): Builder
    {
        return $query->whereIn($this->qualifyColumn('type'), ['Food', 'Bottle', 'Drink']);
    }

    public function scopeMedical(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('classification'), 'FPS.Consumable.Medical');
    }

    public function scopeMiningModifiers(Builder $query): Builder
    {
        return $query->whereIn($this->qualifyColumn('classification'), [
            'Mining.Module',
            'Mining.Gadget',
        ]);
    }

    public function scopeWeaponAttachments(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('type'), 'WeaponAttachment')
            ->whereNotIn(
                $this->qualifyColumn('sub_type'),
                [
                    // Magazines
                    'Magazine',
                    'Missile',

                    // Ship Weapon Attachments
                    'FiringMechanism',
                    'Ventilation',
                    'PowerArray',
                ]
            )
            ->whereNot($this->qualifyColumn('classification'), 'Ship.WeaponAttachment.Barrel');
    }

    public function scopePersonalWeapons(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('type'), 'WeaponPersonal');
    }

    public function scopeClothes(Builder $query): Builder
    {
        return $query
            ->where($this->qualifyColumn('classification'), 'LIKE', 'FPS.Clothing.%');
    }

    public function scopeFpsItems(Builder $query): Builder
    {
        return $query
            ->where($this->qualifyColumn('classification'), 'LIKE', 'FPS.%');
    }

    public function scopeArmor(Builder $query): Builder
    {
        return $query
            ->where($this->qualifyColumn('classification'), 'LIKE', 'FPS.Armor.%');
    }

    public function scopeVehicleWeapons(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('type'), 'WeaponGun');
    }

    public function scopeVehicleItems(Builder $query): Builder
    {
        return $query
            // ->where('class_name', 'NOT LIKE', '%test%')
            // ->where('class_name', 'NOT LIKE', '%lowpoly%')
            // ->where('class_name', 'NOT LIKE', '%dummy%')
            // ->where('class_name', 'NOT LIKE', '%_mm')
            // ->where('class_name', 'NOT LIKE', '%s%_idris_m')
            // ->where('class_name', 'NOT LIKE', '%s%_turret')
            // ->where('class_name', 'NOT LIKE', 'mrck_s05_orig_%')
            // ->where('class_name', 'NOT LIKE', 'mrck_s05_behr_quad_s03_a')
            ->whereIn($this->qualifyColumn('type'), [
                'Arm',
                'Armor',
                'Battery',
                'BombLauncher',
                'Bomb',
                'Cooler',
                'EMP',
                'ExternalFuelTank',
                'FlightController',
                'Flair_Cockpit',
                'Flair_Wall',
                'Flair_Floor',
                'Flair_Surface',
                'FuelIntake',
                'FuelTank',
                'JumpDrive',
                'MainThruster',
                'ManneuverThruster',
                'MiningArm',
                'MiningLaser',
                'Missile',
                'MissileLauncher',
                'Mount',
                'Paints',
                'PowerPlant',
                'QuantumDrive',
                'QuantumFuelTank',
                'QuantumInterdictionGenerator',
                'Radar',
                'SalvageModifier',
                'SelfDestruct',
                'Shield',
                'ShieldController',
                'ToolArm',
                'TowingBeam',
                'TractorBeam',
                'Turret',
                'TurretBase',
                'UtilityTurret',
                'WeaponDefensive',
                'WeaponGun',
                'WeaponMount',
                'WeaponMining',
                'WheeledController',
            ]);
    }

    public function scopeVehicleFlairItems(Builder $query): Builder
    {
        return $query
            ->whereIn($this->qualifyColumn('type'), [
                'Flair_Cockpit',
                'Flair_Wall',
                'Flair_Floor',
                'Flair_Surface',
            ]);
    }

    public function scopeVehicleComponents(Builder $query): Builder
    {
        return $query
            ->whereIn($this->qualifyColumn('type'), [
                'Cooler',
                'Shield',
                'PowerPlant',
                'QuantumDrive',
            ]);
    }

    public function scopePlayerRelevant(Builder $query): Builder
    {
        return $query->where($this->getTable().'.is_player_relevant', true);
    }

    public function scopeWithDescriptionValue(Builder $query, string $name, string $value): Builder
    {
        return $query->whereHas('descriptionData', function (Builder $builder) use ($name, $value) {
            $builder->where('name', $name)->where('value', $value);
        });
    }

    public function scopeWithUexPrices(Builder $query): Builder
    {
        return $query->whereNotNull('uex_prices');
    }

    /**
     * @param  Collection<int, self>  $itemDataCollection
     */
    public static function loadCraftingBlueprints(Collection $itemDataCollection, bool $full = false): void
    {
        $versionIds = $itemDataCollection
            ->pluck('game_version_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($versionIds === []) {
            $itemDataCollection->each(fn (self $itemData) => $itemData->setRelation('craftingBlueprints', collect()));

            return;
        }

        $itemDataCollection->load(['craftingBlueprints' => function ($query) use ($versionIds, $full): void {
            $query->whereIn('game_blueprint_data.game_version_id', $versionIds)
                ->orderByDesc('game_blueprint_data.is_available_by_default')
                ->orderBy('game_blueprint_data.output_name')
                ->orderBy('game_blueprint_data.key')
                ->orderBy('game_blueprint_data.blueprint_id');

            if ($full) {
                $query->with(['blueprint', 'gameVersion', 'ingredients', 'dismantleReturns', 'missions.mission']);
            } else {
                $query->with('blueprint:id,uuid');
            }
        }]);
    }

    /**
     * @param  array<string, bool>  $ambiguousOutputNames
     * @return array{uuid: string, name: string}
     */
    private function craftingBlueprintSummary(BlueprintData $blueprintData, array $ambiguousOutputNames): array
    {
        return [
            'uuid' => $blueprintData->blueprint->uuid,
            'name' => $this->resolveCraftingBlueprintName($blueprintData, $ambiguousOutputNames),
        ];
    }

    /**
     * @param  array<string, bool>  $ambiguousOutputNames
     */
    private function resolveCraftingBlueprintName(BlueprintData $blueprintData, array $ambiguousOutputNames): string
    {
        $outputName = $this->normalizeCraftingBlueprintLabel($blueprintData->output_name);

        if ($outputName !== null && ! isset($ambiguousOutputNames[$outputName])) {
            return $outputName;
        }

        foreach ([$blueprintData->key, $blueprintData->blueprint->uuid, $outputName] as $candidate) {
            $normalizedCandidate = $this->normalizeCraftingBlueprintLabel($candidate);

            if ($normalizedCandidate !== null) {
                return $normalizedCandidate;
            }
        }

        return $blueprintData->blueprint->uuid;
    }

    private function normalizeCraftingBlueprintLabel(mixed $candidate): ?string
    {
        if (! is_string($candidate)) {
            return null;
        }

        $candidate = trim($candidate);

        return $candidate !== '' ? $candidate : null;
    }

    public static function formatGrade(?int $grade, ?string $classification = null): string|int|null
    {
        if ($grade === null) {
            return null;
        }

        if (! str_starts_with($classification ?? '', 'Ship.')) {
            return $grade;
        }

        return match ($grade) {
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            default => $grade,
        };
    }
}
