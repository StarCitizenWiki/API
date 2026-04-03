<?php

declare(strict_types=1);

namespace App\Models\Game;

use Database\Factories\Game\StarmapLocationDataFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
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
        'location_hierarchy_entity_tag_id',
        'name',
        'description',
        'type_name',
        'type_classification',
        'respawn_location_type',
        'size',
        'minimum_display_size',
        'is_scannable',
        'hide_in_starmap',
        'hide_in_world',
        'block_travel',
        'jurisdiction_name',
        'jurisdiction_is_prison',
        'affiliation_name',
        'quantum_travel',
        'asteroid_ring',
        'data',
    ];

    protected $casts = [
        'game_version_id' => 'integer',
        'starmap_location_id' => 'integer',
        'parent_data_id' => 'integer',
        'location_hierarchy_entity_tag_id' => 'integer',
        'size' => 'float',
        'minimum_display_size' => 'float',
        'is_scannable' => 'boolean',
        'hide_in_starmap' => 'boolean',
        'hide_in_world' => 'boolean',
        'block_travel' => 'boolean',
        'jurisdiction_is_prison' => 'boolean',
        'quantum_travel' => 'array',
        'asteroid_ring' => 'array',
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
}
