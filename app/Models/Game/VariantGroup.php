<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VariantGroup extends Model
{
    protected $table = 'game_item_variant_groups';

    protected $fillable = [
        'game_version_id',
        'set_name',
    ];

    public function gameVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'game_version_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VariantGroupItem::class, 'variant_group_id');
    }

    public function baseItem(): HasOne
    {
        return $this->hasOne(VariantGroupItem::class, 'variant_group_id')->where('is_base', true);
    }
}
