<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification\MiningModule;

use App\Models\SC\CommodityItem;
use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MiningModule extends CommodityItem
{
    use HasFactory;

    protected $table = 'sc_item_mining_modules';

    protected $fillable = [
        'item_uuid',
        'type',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class,
            'item_uuid',
            'uuid'
        );
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(MiningModuleModifier::class);
    }
}
