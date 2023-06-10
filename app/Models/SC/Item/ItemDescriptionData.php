<?php

declare(strict_types=1);

namespace App\Models\SC\Item;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemDescriptionData extends Model
{
    use HasFactory;

    protected $table = 'sc_item_description_data';

    protected $fillable = [
        'item_uuid',
        'name',
        'value',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class,
            'item_uuid',
            'uuid'
        );
    }
}
