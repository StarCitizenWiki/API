<?php

declare(strict_types=1);

namespace App\Models\SC\Char\PersonalWeapon;

use Illuminate\Database\Eloquent\Builder;

class IronSight extends Attachment
{
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(
            'type',
            static function (Builder $builder) {
                $builder->where('type', 'WeaponAttachment')
                ->where('sub_type', 'IronSight');
            }
        );
    }

    public function getMagnificationAttribute()
    {
        return $this->getDescriptionDatum('Magnification');
    }

    public function getOpticTypeAttribute()
    {
        return $this->getDescriptionDatum('Type');
    }
}
