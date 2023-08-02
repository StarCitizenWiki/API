<?php

declare(strict_types=1);

namespace App\Models\SC\Char\Clothing;

use App\Models\SC\Item\Item;
use App\Traits\HasBaseVersionsV2Trait;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clothing extends Item
{
    use HasBaseVersionsV2Trait;

    public function __construct(array $attributes = [])
    {
        $this->with = collect($this->with)->merge([
            'resistances',
            'ports',
        ])
            ->unique()
            ->toArray();

        parent::__construct($attributes);
    }

    public function getRouteKey()
    {
        return $this->uuid;
    }

    public function resistances(): HasMany
    {
        return $this->hasMany(
            ClothingResistance::class,
            'item_uuid',
            'uuid',
        );
    }

    public function damageResistances(): HasMany
    {
        return $this->resistances()->whereNotIn('type', ['temp_min', 'temp_max', 'damage_reduction']);
    }

    public function getTempResistanceMinAttribute()
    {
        return $this->resistances()
            ->where('type', 'temp_min')
            ->first()
            ?->threshold;
    }

    public function getTempResistanceMaxAttribute()
    {
        return $this->resistances()
            ->where('type', 'temp_max')
            ->first()
            ?->threshold;
    }

    public function getDamageReductionAttribute()
    {
        return $this->resistances()
            ->where('type', 'damage_reduction')
            ->first()
            ->multiplier ?? null;
    }


    public function getClothingTypeAttribute(): string
    {
        if (str_contains($this->type, 'Char_Armor')) {
            return match (true) {
                str_contains($this->type, 'Helmet') => 'Helmet',
                str_contains($this->type, 'Backpack') => 'Backpack',
                str_contains($this->name, 'Arms') => 'Arms',
                str_contains($this->name, 'Legs') => 'Legs',
                str_contains($this->name, 'Torso') => 'Torso',
                str_contains($this->name, 'Undersuit') => 'Undersuit',
                default => match (true) {
                    default => 'Unknown Type',
                },
            };
        }

        return match (true) {
            str_contains($this->name, 'T-Shirt'), str_contains($this->name, 'Shirt') => 'T-Shirt',
            str_contains($this->name, 'Jacket') => 'Jacket',
            str_contains($this->name, 'Gloves') => 'Gloves',
            str_contains($this->name, 'Pants') => 'Pants',
            str_contains($this->name, 'Bandana') => 'Bandana',
            str_contains($this->name, 'Beanie') => 'Beanie',
            str_contains($this->name, 'Boots') => 'Boots',
            str_contains($this->name, 'Sweater') => 'Sweater',
            str_contains($this->name, 'Hat') => 'Hat',
            str_contains($this->name, 'Shoes') => 'Shoes',
            str_contains($this->name, 'Head Cover') => 'Head Cover',
            str_contains($this->name, 'Gown') => 'Gown',
            str_contains($this->name, 'Slippers') => 'Slippers',
            default => match (true) {
                str_contains($this->type, 'Backpack') => 'Backpack',
                str_contains($this->type, 'Feet') => 'Shoes',
                str_contains($this->type, 'Hands') => 'Gloves',
                str_contains($this->type, 'Hat') => 'Hat',
                str_contains($this->type, 'Legs') => 'Pants',
                str_contains($this->type, 'Torso_0') => 'Shirt',
                str_contains($this->type, 'Torso_1') => 'Jacket',
                default => 'Unknown Type',
            },
        };
    }
}
