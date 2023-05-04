<?php

declare(strict_types=1);

namespace App\Models\SC\Item;

use App\Models\System\Translation\AbstractTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ItemTranslation extends AbstractTranslation
{
    use HasFactory;

    protected $table = 'sc_item_translations';

    protected $fillable = [
        'locale_code',
        'item_uuid',
        'translation',
    ];

    public function items(): BelongsTo
    {
        return $this->belongsTo(
            Item::class,
            'item_uuid',
            'uuid'
        );
    }
}
