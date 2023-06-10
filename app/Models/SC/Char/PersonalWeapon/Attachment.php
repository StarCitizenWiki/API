<?php

declare(strict_types=1);

namespace App\Models\SC\Char\PersonalWeapon;

use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Builder;

class Attachment extends Item
{
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(
            'type',
            static function (Builder $builder) {
                $builder->where('type', 'WeaponAttachment')
                    ->where('name', 'NOT LIKE', '%PLACEHOLDER%');
            }
        );
    }

    public function getAttachmentPointAttribute()
    {
        return $this->getDescriptionDatum('Attachment Point');
    }

    public function getSizeAttribute()
    {
        return $this->getDescriptionDatum('Size');
    }
}
