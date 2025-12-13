<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemTranslation extends Model
{
    use HasFactory;

    protected $table = 'game_item_translations';

    protected $fillable = [
        'locale_code',
        'item_data_id',
        'translation',
    ];

    public function itemData(): BelongsTo
    {
        return $this->belongsTo(ItemData::class);
    }
}
