<?php

declare(strict_types=1);

namespace App\Models\Game;

use App\Models\Game\Mission\MissionData;
use App\Models\Game\Resource\ResourceLocation;
use Database\Factories\Game\StarmapLocationDataFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StarmapLocationData extends Model
{
    /** @use HasFactory<StarmapLocationDataFactory> */
    use HasFactory;

    use HasGameVersion;

    protected $table = 'game_starmap_location_data';

    protected $fillable = [
        'starmap_location_id',
        'game_version_id',
        'parent_data_id',
        'star_data_id',
        'provider_data_id',
        'location_hierarchy_entity_tag_id',
        'name',
        'description',
        'type_name',
        'system',
        'size',
        'is_scannable',
        'block_travel',
        'data',
        'location_uuid',
        'location_slug',
        'parent_name',
        'star_system_name',
        'type_classification',
        'jurisdiction_name',
        'affiliation_name',
        'respawn_location_type',
        'hide_in_starmap',
        'hide_in_world',
        'hide_minor_locations',
        'parent_type_name',
        'parent_location_uuid',
        'parent_location_slug',
        'star_name',
        'star_type_name',
        'star_location_uuid',
        'star_location_slug',
        'has_resources',
        'child_count',
        'tag_name',
        'tag_uuid',
    ];

    protected $casts = [
        'game_version_id' => 'integer',
        'starmap_location_id' => 'integer',
        'parent_data_id' => 'integer',
        'star_data_id' => 'integer',
        'provider_data_id' => 'integer',
        'location_hierarchy_entity_tag_id' => 'integer',
        'size' => 'float',
        'system' => 'string',
        'is_scannable' => 'boolean',
        'block_travel' => 'boolean',
        'mission_count' => 'integer',
        'data' => 'array',
        'location_uuid' => 'string',
        'location_slug' => 'string',
        'parent_name' => 'string',
        'star_system_name' => 'string',
        'type_classification' => 'string',
        'jurisdiction_name' => 'string',
        'affiliation_name' => 'string',
        'respawn_location_type' => 'string',
        'hide_in_starmap' => 'boolean',
        'hide_in_world' => 'boolean',
        'hide_minor_locations' => 'boolean',
        'parent_type_name' => 'string',
        'parent_location_uuid' => 'string',
        'parent_location_slug' => 'string',
        'star_name' => 'string',
        'star_type_name' => 'string',
        'star_location_uuid' => 'string',
        'star_location_slug' => 'string',
        'has_resources' => 'boolean',
        'child_count' => 'integer',
        'tag_name' => 'string',
        'tag_uuid' => 'string',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(StarmapLocation::class, 'starmap_location_id')
            ->select([
                'game_starmap_locations.id',
                'game_starmap_locations.uuid',
                'game_starmap_locations.slug',
            ]);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_data_id');
    }

    public function star(): BelongsTo
    {
        return $this->belongsTo(self::class, 'star_data_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_data_id')->orderBy('name');
    }

    public function locationHierarchyEntityTag(): BelongsTo
    {
        return $this->belongsTo(EntityTag::class, 'location_hierarchy_entity_tag_id');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(
            StarmapAmenity::class,
            'game_starmap_location_data_amenity',
            'location_data_id',
            'amenity_id'
        )->orderBy('display_name');
    }

    public function resourceLocations(): BelongsToMany
    {
        return $this->belongsToMany(
            ResourceLocation::class,
            'game_resource_location_placements',
            'starmap_location_data_id',
            'resource_location_id',
        );
    }

    public function missions(): BelongsToMany
    {
        return $this->belongsToMany(
            MissionData::class,
            'game_mission_data_starmap_location',
            'starmap_location_data_id',
            'mission_data_id',
        )->withPivot('purpose');
    }

    protected function designation(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->tag_name === null) {
                    return null;
                }

                return self::formatDesignation($this->tag_name);
            },
        );
    }

    public static function formatDesignation(string $tagName): ?string
    {
        if (! preg_match('/^([A-Za-z]+?)(\d+)([a-z]?)$/', $tagName, $matches)) {
            return null;
        }

        $prefix = $matches[1];
        $number = (int) $matches[2];
        $suffix = $matches[3];

        $roman = self::toRoman($number);

        return $prefix.' '.$roman.$suffix;
    }

    private static function toRoman(int $number): string
    {
        $map = [
            50 => 'L',
            40 => 'XL',
            10 => 'X',
            9 => 'IX',
            5 => 'V',
            4 => 'IV',
            1 => 'I',
        ];

        $result = '';
        foreach ($map as $value => $numeral) {
            while ($number >= $value) {
                $result .= $numeral;
                $number -= $value;
            }
        }

        return $result;
    }
}
