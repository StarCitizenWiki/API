<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantGroupItem extends Model
{
    public $timestamps = false;

    protected $table = 'game_item_variant_group_items';

    protected $fillable = [
        'variant_group_id',
        'item_data_id',
        'variant_name',
        'sort_order',
        'is_base',
    ];

    protected $casts = [
        'is_base' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function variantGroup(): BelongsTo
    {
        return $this->belongsTo(VariantGroup::class, 'variant_group_id');
    }

    public function itemData(): BelongsTo
    {
        return $this->belongsTo(ItemData::class, 'item_data_id');
    }
}
