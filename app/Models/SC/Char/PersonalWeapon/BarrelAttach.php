<?php

declare(strict_types=1);

namespace App\Models\SC\Char\PersonalWeapon;

use Illuminate\Database\Eloquent\Builder;

class BarrelAttach extends Attachment
{
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(
            'type',
            static function (Builder $builder) {
                $builder->where('type', 'WeaponAttachment')
                    ->where('sub_type', 'Barrel');
            }
        );
    }

    public function getTypeAttribute()
    {
        return $this->getDescriptionDatum('Type');
    }
}
