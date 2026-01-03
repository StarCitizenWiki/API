<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $table = 'game_items';

    protected $fillable = [
        'uuid',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function data(): HasMany
    {
        return $this->hasMany(ItemData::class);
    }

    /**
     * Scope: eager load ItemData for a given game version code
     * or for the default version if no code is given.
     */
    public function scopeWithDataForVersion(Builder $query, ?string $gameVersionCode = null): Builder
    {
        $version = GameVersion::resolveRequestedOrDefault($gameVersionCode);

        return $query->with([
            'data' => function ($builder) use ($version) {
                $builder->where('game_version_id', $version->id);
            },
        ]);
    }

    /**
     * Instance helper: get data relationship filtered to a version
     * or to the default version if no code is given.
     */
    public function dataForVersion(?string $gameVersionCode = null): HasMany
    {
        $version = GameVersion::resolveRequestedOrDefault($gameVersionCode);

        return $this->data()->where('game_version_id', $version->id);
    }
}
