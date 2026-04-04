<?php

declare(strict_types=1);

namespace App\Models\Game;

use Database\Factories\Game\StarmapLocationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StarmapLocation extends Model
{
    /** @use HasFactory<StarmapLocationFactory> */
    use HasFactory;

    protected $table = 'game_starmap_locations';

    protected $fillable = [
        'uuid',
    ];

    public function data(): HasMany
    {
        return $this->hasMany(StarmapLocationData::class, 'starmap_location_id');
    }

    public function scopeWithDataForVersion(Builder $query, ?string $gameVersionCode = null): Builder
    {
        $version = GameVersion::resolveRequestedOrDefault($gameVersionCode);

        return $query->with([
            'data' => function ($builder) use ($version) {
                $builder->where('game_version_id', $version->id);
            },
        ]);
    }

    public function dataForVersion(?string $gameVersionCode = null): HasMany
    {
        $version = GameVersion::resolveRequestedOrDefault($gameVersionCode);

        return $this->data()->where('game_version_id', $version->id);
    }
}
