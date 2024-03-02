<?php

declare(strict_types=1);

namespace App\Models\SC\Char\PersonalWeapon;

use App\Models\SC\Item\Item;
use App\Traits\HasDescriptionDataTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Optional;

class PersonalWeapon extends Item
{
    use HasDescriptionDataTrait;

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(
            'type',
            static function (Builder $builder) {
                $builder->where('type', 'WeaponPersonal');
            }
        );
    }

    public function __construct(array $attributes = [])
    {
        $this->with = collect($this->with)->merge([
            'modes',
            'damages',
            'ammunition',
        ])
            ->unique()
            ->toArray();

        parent::__construct($attributes);
    }

    public function getMagazineTypeAttribute(): string
    {
        $magazineAttach = str_replace(
            $this->name,
            '',
            optional($this->magazine)->name ?? ''
        );

        $exploded = explode('(', $magazineAttach);

        return trim($exploded[0]);
    }

    public function modes(): HasMany
    {
        return $this->hasMany(PersonalWeaponMode::class, 'item_uuid', 'uuid');
    }

    /**
     * @return Optional|null
     */
    public function getMagazineAttribute()
    {
        $magazine = $this->ports()->where('name', 'LIKE', '%magazine%')->first();

        if ($magazine !== null) {
            return $magazine?->item?->specification;
        }

        return optional();
    }

    public function ammunition(): HasOne
    {
        return $this->hasOne(PersonalWeaponAmmunition::class, 'item_uuid', 'uuid');
    }

    public function attachments(): HasMany
    {
        return $this->ports();
    }

    public function damages(): HasManyThrough
    {
        return $this->hasManyThrough(
            PersonalWeaponAmmunitionDamage::class,
            PersonalWeaponAmmunition::class,
            'item_uuid',
            'ammunition_id',
            'uuid',
            'id',
        );
    }

    public function getRofAttribute()
    {
        return $this->getDescriptionDatum('Rate Of Fire');
    }

    public function getWeaponClassAttribute()
    {
        return $this->getDescriptionDatum('Class');
    }

    public function getEffectiveRangeAttribute()
    {
        return $this->getDescriptionDatum('Effective Range');
    }

    public function getWeaponTypeAttribute()
    {
        return $this->getDescriptionDatum('Item Type');
    }
}
