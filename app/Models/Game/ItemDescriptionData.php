<?php

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemDescriptionData extends Model
{
    protected $table = 'game_item_description_data';

    protected $fillable = [
        'item_id',
        'name',
        'value',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
