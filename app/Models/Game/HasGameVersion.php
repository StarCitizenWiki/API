<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasGameVersion
{
    public function gameVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'game_version_id');
    }

    public function scopeForRequestedOrDefaultVersion(Builder $query, ?string $code = null): Builder
    {
        $version = GameVersion::resolveRequestedOrDefault($code);

        return $query->where($query->qualifyColumn('game_version_id'), $version->id);
    }
}
