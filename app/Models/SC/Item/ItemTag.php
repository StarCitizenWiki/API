<?php

declare(strict_types=1);

namespace App\Models\SC\Item;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ItemTag extends Pivot
{
    protected $table = 'sc_item_tag';

    protected $fillable = [
        'is_required_tag',
        'item_id',
        'tag_id',
    ];

    public $timestamps = false;

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(
            Tag::class,
            'tag_id',
            'id'
        );
    }
}
