<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MiningModule extends Item
{
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(
            'type',
            static function (Builder $builder) {
                $builder->where('type', 'MiningModifier')
                    ->where('sub_type', 'Gun');
            }
        );
    }

    public function __construct(array $attributes = [])
    {
        $this->with = collect($this->with)->merge([
            'modifiers',
        ])
            ->unique()
            ->toArray();

        parent::__construct($attributes);
    }

    public function modifiers(): HasMany
    {
        return $this->descriptionData()->whereNotIn(
            'sc_item_description_data.name',
            [
                'Manufacturer',
                'Item Type',
                'Uses',
                'Duration',
            ]
        );
    }

    public function getDurationAttribute()
    {
        return $this->getDescriptionDatum('Duration');
    }

    public function getUsesAttribute()
    {
        return $this->getDescriptionDatum('Uses');
    }
}
