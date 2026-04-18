<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactionStanding extends Model
{
    use HasFactory;

    protected $table = 'game_faction_standings';

    protected $fillable = [
        'uuid',
        'faction_scope_id',
        'name',
        'display_name',
        'min_reputation',
        'drift_reputation',
        'drift_time_hours',
        'gated',
    ];

    protected $casts = [
        'min_reputation' => 'integer',
        'drift_reputation' => 'integer',
        'drift_time_hours' => 'integer',
        'gated' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(FactionScope::class, 'faction_scope_id');
    }
}
