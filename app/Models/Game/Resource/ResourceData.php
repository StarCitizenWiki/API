<?php

declare(strict_types=1);

namespace App\Models\Game\Resource;

use App\Enums\Game\ResourceKind;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use Database\Factories\Game\Resource\ResourceDataFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceData extends Model
{
    /** @use HasFactory<ResourceDataFactory> */
    use HasFactory;

    protected $table = 'game_resource_data';

    protected $fillable = [
        'resource_id',
        'game_version_id',
        'key',
        'name',
        'kind',
        'tier',
        'signature',
        'data',
    ];

    protected $casts = [
        'game_version_id' => 'integer',
        'kind' => ResourceKind::class,
        'signature' => 'integer',
        'data' => AsCollection::class,
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function gameVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'game_version_id');
    }

    public function commodities(): BelongsToMany
    {
        return $this->belongsToMany(
            Commodity::class,
            'game_resource_commodity',
            'resource_data_id',
            'commodity_id',
        )->using(ResourceCommodity::class)->withPivot([
            'weight',
            'min_percentage',
            'max_percentage',
            'probability',
            'quality_scale',
            'curve_exponent',
        ])->withTimestamps();
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ResourceLocation::class);
    }

    public function scopeForRequestedOrDefaultVersion(Builder $query, ?string $code = null): Builder
    {
        if ($code !== null) {
            return $query->whereHas('gameVersion', static function (Builder $q) use ($code): void {
                $q->whereRaw('LOWER(code) = ?', [strtolower($code)]);
            });
        }

        return $query->whereHas('gameVersion', static function (Builder $q): void {
            $q->where('is_default', true);
        });
    }
}
