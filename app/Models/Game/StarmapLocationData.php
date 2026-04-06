<?php

declare(strict_types=1);

namespace App\Models\Game;

use App\Models\Game\Resource\ResourceLocation;
use Database\Factories\Game\StarmapLocationDataFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
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

    protected $table = 'game_starmap_location_data';

    protected $fillable = [
        'starmap_location_id',
        'game_version_id',
        'parent_data_id',
        'star_data_id',
        'provider_data_id',
        'location_hierarchy_entity_tag_id',
        'name',
        'slug',
        'description',
        'type_name',
        'system',
        'size',
        'is_scannable',
        'block_travel',
        'data',
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
        'data' => AsCollection::class,
    ];

    public function scopeForRequestedOrDefaultVersion(Builder $query, ?string $code = null): Builder
    {
        if ($code !== null) {
            return $query->whereHas('gameVersion', function (Builder $builder) use ($code) {
                $builder->whereRaw('LOWER(code) = ?', [strtolower($code)]);
            });
        }

        return $query->whereHas('gameVersion', function (Builder $builder) {
            $builder->where('is_default', true);
        });
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(StarmapLocation::class, 'starmap_location_id');
    }

    public function gameVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'game_version_id');
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

    protected function jurisdictionName(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => data_get($this->data, 'Jurisdiction.Name'),
        );
    }

    protected function affiliationName(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => data_get($this->data, 'Affiliation.DisplayName'),
        );
    }

    protected function designation(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->locationHierarchyEntityTag === null) {
                    return null;
                }

                return self::formatDesignation($this->locationHierarchyEntityTag->name);
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
