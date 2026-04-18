<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Faction extends Model
{
    use HasFactory;

    protected $table = 'game_factions';

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'default_reaction',
        'faction_type',
        'able_to_arrest',
        'polices_lawful_trespass',
        'polices_criminality',
        'no_legal_rights',
        'has_reputation',
        'headquarters',
        'founded',
        'leadership',
        'area',
        'focus',
        'lawful',
        'sort_order_scope',
        'is_npc',
        'hide_in_delphi_app',
    ];

    protected $casts = [
        'able_to_arrest' => 'boolean',
        'polices_lawful_trespass' => 'boolean',
        'polices_criminality' => 'boolean',
        'no_legal_rights' => 'boolean',
        'has_reputation' => 'boolean',
        'lawful' => 'boolean',
        'is_npc' => 'boolean',
        'hide_in_delphi_app' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function reputationRef(): HasOne
    {
        return $this->hasOne(FactionReputationRef::class);
    }
}
