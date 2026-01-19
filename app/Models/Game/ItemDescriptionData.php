<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemDescriptionData extends Model
{
    use HasFactory;

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
