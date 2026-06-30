<?php

declare(strict_types=1);

namespace App\Support\Game;

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Database\Eloquent\Model;

/**
 * Configuration map for entity types that support version diffing.
 *
 * Each entry defines:
 * - model: The parent entity model class (morph target)
 * - data_model: The versioned data model class
 * - foreign_key: The FK column on the data table pointing to the parent
 * - columns: Scalar columns to diff (excludes internal IDs and descriptions)
 * - route_name: The web route for showing the entity
 * - label: Human-readable label for UI
 */
class EntityTypeConfig
{
    /** @var array<string, array{model: class-string<Model>, data_model: class-string<Model>, foreign_key: string, columns: string[], route_name: string, route_param: string, label: string}>|null */
    private static ?array $cache = null;

    /**
     * @return array<string, array{model: class-string<Model>, data_model: class-string<Model>, foreign_key: string, columns: string[], route_name: string, route_param: string, label: string}>
     */
    public static function all(): array
    {
        return self::$cache ??= [
            'item' => [
                'model' => Item::class,
                'data_model' => ItemData::class,
                'foreign_key' => 'item_id',
                'columns' => ['name', 'class_name', 'type', 'sub_type', 'classification', 'size', 'grade', 'rarity'],
                'route_name' => 'web.items.show',
                'route_param' => 'item',
                'label' => 'Items',
            ],
            'vehicle' => [
                'model' => Vehicle::class,
                'data_model' => VehicleData::class,
                'foreign_key' => 'vehicle_id',
                'columns' => ['class_name', 'name', 'display_name', 'career', 'role', 'is_vehicle', 'is_gravlev', 'is_spaceship', 'is_power_suit', 'size'],
                'route_name' => 'web.vehicles.show',
                'route_param' => 'vehicle',
                'label' => 'Vehicles',
            ],
            'blueprint' => [
                'model' => Blueprint::class,
                'data_model' => BlueprintData::class,
                'foreign_key' => 'blueprint_id',
                'columns' => ['output_name', 'output_class', 'craft_time_seconds', 'is_available_by_default'],
                'route_name' => 'web.blueprints.show',
                'route_param' => 'blueprint',
                'label' => 'Blueprints',
            ],
            'mission' => [
                'model' => Mission::class,
                'data_model' => MissionData::class,
                'foreign_key' => 'mission_id',
                'columns' => ['debug_name', 'mission_type', 'title', 'faction_id', 'entry_type', 'handler_type', 'illegal', 'shareable', 'once_only', 'available_in_prison', 'not_for_release', 'work_in_progress', 'rank_index'],
                'route_name' => 'web.missions.show',
                'route_param' => 'mission',
                'label' => 'Missions',
            ],
            'starmap_location' => [
                'model' => StarmapLocation::class,
                'data_model' => StarmapLocationData::class,
                'foreign_key' => 'starmap_location_id',
                'columns' => ['name', 'type_name', 'system', 'size', 'is_scannable', 'block_travel'],
                'route_name' => 'web.locations.show',
                'route_param' => 'identifier',
                'label' => 'Locations',
            ],
        ];
    }

    /**
     * Get config for a specific entity type key.
     *
     * @return array{model: class-string<Model>, data_model: class-string<Model>, foreign_key: string, columns: string[], route_name: string, route_param: string, label: string}|null
     */
    public static function get(string $type): ?array
    {
        return static::all()[$type] ?? null;
    }

    /**
     * Get the entity type key for a given morph class.
     */
    public static function keyForModel(string $modelClass): ?string
    {
        return array_find_key(static::all(), fn ($config) => $config['model'] === $modelClass);
    }

    /**
     * Get the morph class for a given entity type key.
     *
     * @return class-string<Model>
     */
    public static function modelClass(string $type): string
    {
        return static::get($type)['model'];
    }
}
