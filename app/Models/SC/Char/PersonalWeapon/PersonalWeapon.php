<?php

declare(strict_types=1);

namespace App\Models\SC\Char\PersonalWeapon;

use App\Models\SC\Ammunition\Ammunition;
use App\Models\SC\Ammunition\AmmunitionDamage;
use App\Models\SC\Item\Item;
use App\Traits\HasDescriptionDataTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
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

    public function getMagazineAttribute(): Model|Optional|null
    {
        $magazine = $this->ports()->where('name', 'LIKE', '%magazine%')->first();

        if ($magazine !== null) {
            return $magazine->item?->specification;
        }

        return optional();
    }

    public function getAmmunitionAttribute(): ?Ammunition
    {
        return $this->magazine?->ammunition;
    }

    public function attachments(): HasMany
    {
        return $this->ports();
    }

    /**
     * @return Collection<AmmunitionDamage>
     */
    public function damages(): Collection
    {
        return $this->getAmmunitionAttribute()?->damages ?? collect();
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
