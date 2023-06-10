<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\SC\Item\Item;

/**
 * Trait HasModelChangelogTrait
 */
trait HasDescriptionDataTrait
{
    public function getDescriptionDatum(string $name)
    {
        if ($this instanceof Item) {
            $relation = $this->descriptionData();
        } else {
            $relation = $this->item->descriptionData();
        }

        return $relation
            ->where('name', $name)
            ->first()?->value;
    }

    public function getDescriptionTypeAttribute()
    {
        return $this->getDescriptionDatum('Type');
    }

    public function getDescriptionManufacturerAttribute()
    {
        return $this->getDescriptionDatum('Manufacturer');
    }
}
