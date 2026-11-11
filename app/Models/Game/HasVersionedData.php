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

    /**
     * @return list<string>
     */
    public function versionsWithData(): array
    {
        $data = $this->data()->getRelated();

        return $this->data()
            ->join('game_versions', 'game_versions.id', '=', $data->qualifyColumn('game_version_id'))
            ->where('game_versions.is_hidden', false)
            // Undated versions are upcoming and sort first as newest.
            ->orderByRaw('game_versions.released_at DESC NULLS FIRST')
            ->orderByDesc('game_versions.id')
            ->pluck('game_versions.code')
            ->values()
            ->all();
    }
}
