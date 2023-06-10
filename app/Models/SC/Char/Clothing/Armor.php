<?php

declare(strict_types=1);

namespace App\Models\SC\Char\Clothing;

use Illuminate\Database\Eloquent\Builder;

class Armor extends Clothing
{
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(
            'type',
            static function (Builder $builder) {
                $builder->where('type', 'LIKE', 'Char_Armor%');
            }
        );
    }
}
