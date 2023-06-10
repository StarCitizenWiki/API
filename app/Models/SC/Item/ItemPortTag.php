<?php

declare(strict_types=1);

namespace App\Models\SC\Item;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ItemPortTag extends Pivot
{
    protected $table = 'sc_item_port_tag';

    protected $fillable = [
        'is_required_tag',
        'item_port_id',
        'tag_id',
    ];

    public function itemPort(): BelongsTo
    {
        return $this->belongsTo(
            ItemPort::class,
            'item_port_id',
            'id'
        );
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
