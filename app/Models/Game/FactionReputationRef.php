<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactionReputationRef extends Model
{
    use HasFactory;

    protected $table = 'game_faction_reputation_refs';

    protected $fillable = [
        'faction_id',
        'hostility_standing_id',
        'allied_standing_id',
    ];

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function hostilityStanding(): BelongsTo
    {
        return $this->belongsTo(FactionStanding::class, 'hostility_standing_id');
    }

    public function alliedStanding(): BelongsTo
    {
        return $this->belongsTo(FactionStanding::class, 'allied_standing_id');
    }
}
