<?php

declare(strict_types=1);

namespace App\Models\SC\Char\Clothing;

use App\Models\SC\CommodityItem;
use App\Traits\HasBaseVersionsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clothes extends Clothing
{
    use HasFactory;

    /**
     * Limits Clothes to Armors
     */
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(
            'type',
            static function (Builder $builder) {
                $builder->whereRelation('item', 'type', 'LIKE', 'Char_Clothing%');
            }
        );
    }
}
