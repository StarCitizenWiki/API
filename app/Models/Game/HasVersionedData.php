<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasVersionedData
{
    public function scopeWithDataForVersion(Builder $query, ?string $gameVersionCode = null): Builder
    {
        $version = GameVersion::resolveRequestedOrDefault($gameVersionCode);

        return $query->with([
            'data' => static function ($builder) use ($version): void {
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
