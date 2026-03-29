<?php

declare(strict_types=1);

namespace App\Models\Game;

use Database\Factories\Game\ResourceTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ResourceType extends Model
{
    /** @use HasFactory<ResourceTypeFactory> */
    use HasFactory;

    protected $table = 'game_resource_types';

    protected $fillable = [
        'uuid',
        'key',
        'name',
        'description',
        'refined_version_uuid',
        'validate_default_cargo_box',
        'has_default_cargo_containers',
        'box_sizes_scu',
        'data',
    ];

    protected $casts = [
        'validate_default_cargo_box' => 'boolean',
        'has_default_cargo_containers' => 'boolean',
        'box_sizes_scu' => 'array',
        'data' => AsCollection::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function refinedVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'refined_version_uuid', 'uuid');
    }

    public function scopeMatchingLookup(Builder $query, string $searchTerm): Builder
    {
        return $query->where(static function (Builder $builder) use ($searchTerm): void {
            $builder->whereLike('name', '%'.$searchTerm.'%')
                ->orWhereLike('key', '%'.$searchTerm.'%');

            if (Str::isUuid($searchTerm)) {
                $builder->orWhere('uuid', $searchTerm);
            }
        });
    }
}
