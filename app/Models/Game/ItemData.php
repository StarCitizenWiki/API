<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ItemData extends Model
{
    use HasFactory;

    private ?Collection $resolvedCraftingBlueprints = null;

    private bool $hasResolvedCraftingBlueprints = false;

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
        'uex_prices',
    ];

    protected $casts = [
        'size' => 'integer',
        'grade' => 'integer',
        'data' => AsCollection::class,
        'uex_prices' => 'array',
    ];

    public function scopeForRequestedOrDefaultVersion(Builder $query, ?string $code = null): Builder
    {
        if ($code !== null) {
            return $query->whereHas('gameVersion', function (Builder $q) use ($code) {
                $q->whereRaw('LOWER(code) = ?', [strtolower($code)]);
            });
        }

        return $query->whereHas('gameVersion', function (Builder $q) {
            $q->where('is_default', true);
        });
    }

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

    /**
     * @return array<int, array{uuid: string, name: string}>
     */
    public function getBlueprintAttribute(): array
    {
        return $this->craftingBlueprints()
            ->filter(fn (BlueprintData $blueprintData): bool => $blueprintData->blueprint !== null)
            ->map(fn (BlueprintData $blueprintData): array => $this->craftingBlueprintSummary($blueprintData))
            ->values()
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

    public function gameVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'game_version_id');
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
            default => $query,
        };
    }

    public function scopeFood(Builder $query): Builder
    {
        return $query->whereIn('type', ['Food', 'Bottle', 'Drink']);
    }

    public function scopeMedical(Builder $query): Builder
    {
        return $query->where('classification', 'FPS.Consumable.Medical');
    }

    public function scopeMiningModifiers(Builder $query): Builder
    {
        return $query->whereIn('classification', [
            'Mining.Module',
            'Mining.Gadget',
        ]);
    }

    public function scopeWeaponAttachments(Builder $query): Builder
    {
        return $query->where('type', 'WeaponAttachment')
            ->whereNotIn(
                'sub_type',
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
            ->whereNot('classification', 'Ship.WeaponAttachment.Barrel');
    }

    public function scopePersonalWeapons(Builder $query): Builder
    {
        return $query->where('type', 'WeaponPersonal');
    }

    public function scopeClothes(Builder $query): Builder
    {
        return $query
            ->where('classification', 'LIKE', 'FPS.Clothing.%')
            ->excludePlaceholderNames();
    }

    public function scopeFpsItems(Builder $query): Builder
    {
        return $query
            ->where('classification', 'LIKE', 'FPS.%')
            ->excludePlaceholderNames();
    }

    public function scopeArmor(Builder $query): Builder
    {
        return $query
            ->where('classification', 'LIKE', 'FPS.Armor.%')
            ->excludePlaceholderNames();
    }

    public function scopeVehicleWeapons(Builder $query): Builder
    {
        return $query->where('type', 'WeaponGun');
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
            ->whereIn('type', [
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
            ->whereIn('type', [
                'Flair_Cockpit',
                'Flair_Wall',
                'Flair_Floor',
                'Flair_Surface',
            ]);
    }

    public function scopeVehicleComponents(Builder $query): Builder
    {
        return $query
            ->whereIn('type', [
                'Cooler',
                'Shield',
                'PowerPlant',
                'QuantumDrive',
            ]);
    }

    public function scopeExcludePlaceholderNames(Builder $query): Builder
    {
        return $query
            ->where($this->table.'.name', 'NOT LIKE', '%PLACEHOLDER%')
            ->where($this->table.'.name', 'NOT LIKE', '%Placeholder%')
            ->where($this->table.'.name', 'NOT LIKE', 'PH -%')
            ->where($this->table.'.name', 'NOT LIKE', '[PH]%')
            ->where($this->table.'.name', 'NOT LIKE', '%- name%');
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
    public static function hydrateCraftingBlueprints(Collection $itemDataCollection): void
    {
        $itemsWithUuid = $itemDataCollection
            ->filter(
                fn (self $itemData): bool => $itemData->relationLoaded('item')
                    && $itemData->item !== null
                    && is_string($itemData->item->uuid)
                    && $itemData->item->uuid !== ''
            );

        if ($itemsWithUuid->isEmpty()) {
            return;
        }

        $itemUuids = $itemsWithUuid
            ->map(fn (self $itemData): string => $itemData->item->uuid)
            ->unique()
            ->values()
            ->all();

        $gameVersionIds = $itemsWithUuid
            ->pluck('game_version_id')
            ->filter(fn (mixed $gameVersionId): bool => $gameVersionId !== null)
            ->unique()
            ->values()
            ->all();

        $craftingBlueprints = BlueprintData::query()
            ->select([
                'id',
                'blueprint_id',
                'game_version_id',
                'output_item_uuid',
                'output_name',
                'key',
            ])
            ->with('blueprint:id,uuid')
            ->whereIn('output_item_uuid', $itemUuids)
            ->whereIn('game_version_id', $gameVersionIds)
            ->orderByDesc('is_available_by_default')
            ->orderBy('output_name')
            ->orderBy('key')
            ->orderBy('blueprint_id')
            ->get()
            ->groupBy(
                fn (BlueprintData $blueprintData): string => sprintf(
                    '%s:%s',
                    $blueprintData->game_version_id,
                    $blueprintData->output_item_uuid
                )
            );

        $itemsWithUuid->each(function (self $itemData) use ($craftingBlueprints): void {
            $itemData->setRelation(
                'craftingBlueprints',
                $craftingBlueprints->get(
                    sprintf('%s:%s', $itemData->game_version_id, $itemData->item->uuid),
                    collect()
                )
            );
        });
    }

    /**
     * @return Collection<int, BlueprintData>
     */
    private function craftingBlueprints(): Collection
    {
        if ($this->relationLoaded('craftingBlueprints')) {
            /** @var Collection<int, BlueprintData> $craftingBlueprints */
            $craftingBlueprints = $this->getRelation('craftingBlueprints');

            return $craftingBlueprints;
        }

        if ($this->hasResolvedCraftingBlueprints) {
            return $this->resolvedCraftingBlueprints ?? collect();
        }

        $this->hasResolvedCraftingBlueprints = true;

        $itemUuid = $this->relationLoaded('item')
            ? $this->item?->uuid
            : $this->item()->value('uuid');

        if (! is_string($itemUuid) || $itemUuid === '') {
            $this->resolvedCraftingBlueprints = collect();

            return $this->resolvedCraftingBlueprints;
        }

        $this->resolvedCraftingBlueprints = BlueprintData::query()
            ->select([
                'id',
                'blueprint_id',
                'game_version_id',
                'output_item_uuid',
                'output_name',
                'key',
            ])
            ->with('blueprint:id,uuid')
            ->where('game_version_id', $this->game_version_id)
            ->where('output_item_uuid', $itemUuid)
            ->orderByDesc('is_available_by_default')
            ->orderBy('output_name')
            ->orderBy('key')
            ->orderBy('blueprint_id')
            ->get();

        return $this->resolvedCraftingBlueprints;
    }

    /**
     * @return array{uuid: string, name: string}
     */
    private function craftingBlueprintSummary(BlueprintData $blueprintData): array
    {
        return [
            'uuid' => $blueprintData->blueprint->uuid,
            'name' => $this->resolveCraftingBlueprintName($blueprintData),
        ];
    }

    private function resolveCraftingBlueprintName(BlueprintData $blueprintData): string
    {
        foreach ([$blueprintData->output_name, $blueprintData->key, $blueprintData->blueprint->uuid] as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return $blueprintData->blueprint->uuid;
    }
}
