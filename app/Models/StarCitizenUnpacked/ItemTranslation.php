<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\System\Translation\AbstractTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemTranslation extends AbstractTranslation
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_item_translations';

    protected $fillable = [
        'locale_code',
        'item_uuid',
        'translation',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'uuid', 'item_uuid');
    }
}
