<?php

namespace App\Models\SC\Char\PersonalWeapon;

use App\Models\SC\Item\Item;
use App\Traits\HasDescriptionDataTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    protected $with = [
        'modes',
        'ports',
        'damages',
        'ammunition',
    ];

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

    /**
     * @return HasMany
     */
    public function modes(): HasMany
    {
        return $this->hasMany(PersonalWeaponMode::class, 'item_uuid', 'uuid');
    }

    /**
     * @return Optional
     */
    public function getMagazineAttribute(): Optional
    {
        $magazine = $this->ports()->where('name', 'LIKE', '%magazine%')->first();
        if ($magazine !== null) {
            return optional($magazine->item->specification);
        }

        return optional();
    }

    /**
     * @return HasOne
     */
    public function ammunition(): HasOne
    {
        return $this->hasOne(PersonalWeaponAmmunition::class, 'item_uuid', 'uuid');
    }

    /**
     * @return BelongsToMany
     */
    public function attachments()
    {
        return $this->ports();
    }

    /**
     * @return HasManyThrough
     */
    public function damages(): HasManyThrough
    {
        return $this->hasManyThrough(
            PersonalWeaponAmmunitionDamage::class,
            PersonalWeaponAmmunition::class,
            'item_uuid',
            'id',
            'uuid',
            'id',
        );
    }

    public function getBaseModelAttribute(): ?self
    {
        $baseName = preg_replace('/"[\w\s\']+"\s/', '', $this->name);
        return self::query()
            ->where('name', $baseName)
            ->whereNot('name', $this->name)
            ->first();
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
