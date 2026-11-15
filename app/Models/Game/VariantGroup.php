<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VariantGroup extends Model
{
    use HasGameVersion;

    protected $table = 'game_item_variant_groups';

    protected $fillable = [
        'game_version_id',
        'set_name',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(VariantGroupItem::class, 'variant_group_id');
    }
}
