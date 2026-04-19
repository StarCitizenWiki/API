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
        'faction_scope_id',
    ];

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function scope(): BelongsTo
    {
        return $this->belongsTo(FactionScope::class, 'faction_scope_id');
    }
}
