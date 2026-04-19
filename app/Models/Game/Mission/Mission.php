<?php

declare(strict_types=1);

namespace App\Models\Game\Mission;

use App\Models\Game\GameVersion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mission extends Model
{
    use HasFactory;

    protected $table = 'game_missions';

    protected $fillable = [
        'uuid',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function data(): HasMany
    {
        return $this->hasMany(MissionData::class);
    }

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
