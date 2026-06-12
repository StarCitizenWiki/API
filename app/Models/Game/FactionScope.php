<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FactionScope extends Model
{
    use HasFactory;

    protected $table = 'game_faction_scopes';

    protected $fillable = [
        'uuid',
        'scope_name',
        'display_name',
        'reputation_ceiling',
        'initial_reputation',
    ];

    protected $casts = [
        'reputation_ceiling' => 'integer',
        'initial_reputation' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function standings(): HasMany
    {
        return $this->hasMany(FactionStanding::class)
            ->select([
                'game_faction_standings.id',
                'game_faction_standings.faction_scope_id',
                'game_faction_standings.name',
                'game_faction_standings.display_name',
                'game_faction_standings.min_reputation',
            ]);
    }
}
