<?php

declare(strict_types=1);

namespace App\Models\Game;

use Database\Factories\Game\BlueprintDataFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BlueprintData extends Model
{
    /** @use HasFactory<BlueprintDataFactory> */
    use HasFactory;

    protected $table = 'game_blueprint_data';

    protected $perPage = 50;

    protected $fillable = [
        'blueprint_id',
        'game_version_id',
        'key',
        'category_uuid',
        'output_item_uuid',
        'output_name',
        'output_class',
        'craft_time_seconds',
        'is_available_by_default',
        'ingredient_resource_type_uuids',
        'data',
    ];

    protected $casts = [
        'blueprint_id' => 'integer',
        'game_version_id' => 'integer',
        'craft_time_seconds' => 'integer',
        'is_available_by_default' => 'boolean',
        'ingredient_resource_type_uuids' => 'array',
        'data' => AsCollection::class,
    ];

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function gameVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'game_version_id');
    }

    public function outputItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'output_item_uuid', 'uuid');
    }

    public function scopeForRequestedOrDefaultVersion(Builder $query, ?string $code = null): Builder
    {
        if ($code !== null) {
            return $query->whereHas('gameVersion', function (Builder $builder) use ($code): void {
                $builder->whereRaw('LOWER(code) = ?', [strtolower($code)]);
            });
        }

        return $query->whereHas('gameVersion', static function (Builder $builder): void {
            $builder->where('is_default', true);
        });
    }

    public function scopeConsumesResourceType(Builder $query, string $resourceTypeUuid): Builder
    {
        return $query->whereJsonContains('ingredient_resource_type_uuids', $resourceTypeUuid);
    }

    /**
     * @param  array<int, string>  $resourceTypeUuids
     */
    public function scopeConsumesAnyResourceTypes(Builder $query, array $resourceTypeUuids): Builder
    {
        return $query->where(static function (Builder $builder) use ($resourceTypeUuids): void {
            foreach (array_values(array_unique($resourceTypeUuids)) as $index => $resourceTypeUuid) {
                if ($index === 0) {
                    $builder->whereJsonContains('ingredient_resource_type_uuids', $resourceTypeUuid);

                    continue;
                }

                $builder->orWhereJsonContains('ingredient_resource_type_uuids', $resourceTypeUuid);
            }
        });
    }

    public function scopeForOutputItemUuid(Builder $query, string $outputItemUuid): Builder
    {
        return $query->where('output_item_uuid', $outputItemUuid);
    }

    public function scopeForOutputName(Builder $query, string $outputName): Builder
    {
        return $query->whereLike('output_name', '%'.$outputName.'%');
    }

    public function scopeForOutputClass(Builder $query, string $outputClass): Builder
    {
        return $query->whereLike('output_class', '%'.$outputClass.'%');
    }

    public function scopeSearchOutput(Builder $query, string $searchTerm): Builder
    {
        return $query->where(static function (Builder $builder) use ($searchTerm): void {
            $builder->whereLike('output_name', '%'.$searchTerm.'%')
                ->orWhereLike('output_class', '%'.$searchTerm.'%');

            if (Str::isUuid($searchTerm)) {
                $builder->orWhere('output_item_uuid', $searchTerm);
            }
        });
    }
}
