<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;

trait HasBaseVersionsV2Trait
{
    /**
     * Keywords used as splits for Armor Names
     * Essentially removes the color from the item name
     *
     * @var string[]
     */
    public static $splits = [
        'Arms',
        'Helmet',
        'Legs',
        'Core',
        'Undersuit',
        'Backpack',

        'Boots',
        'Jacket',
        'Pants',
        'Beanie',
        'Hat',
        'Head Cover',
        'Gloves',
        'T-Shirt',
        'Shirt',
        'Shoes',
        'Harness',
        'Vest',

        'Scrub Top',
        'Scrub Pants',
        'Slippers',
        'Gown',
    ];

    /**
     * Tries to find the base model of this item
     * Removes the color string from the name and searches all armors
     *
     * @return self|null
     */
    public function getBaseModelAttribute(): ?self
    {
        foreach (self::$splits as $split) {
            if (!Str::contains($this->name, $split)) {
                continue;
            }

            $splitted = array_filter(explode($split, $this->name));

            // This is the base version
            if (count($splitted) !== 2) {
                return null;
            }

            array_pop($splitted);
            $splitted[] = $split;

            $baseName = implode(' ', $splitted);
            $baseName = trim(preg_replace('/\s+/', ' ', $baseName));

            $toSearch = [
                $baseName,
            ];

            if ($this->name !== sprintf('%s Base', $baseName)) {
                $toSearch[] = sprintf('%s Base', $baseName);
            }

            $result = self::query()
                ->whereIn('name', $toSearch)
                ->orWhere('type', $this->type)
                ->where('name', 'LIKE', "{$baseName}%")
                // Ignore Nine-Tails Armor
                ->where('name', 'NOT LIKE', '%Modified%')
                // Ignore Versions with " in their name
                ->where('name', 'NOT LIKE', '%"%')
                ->orderBy('name')
                ->get();

            $base = $result->first(function ($value) use ($baseName) {
                return Str::contains($value->name, 'Base') || $value->name === $baseName;
            });

            $base = $base ?? $result->first();
            if (optional($base)->name === $this->name) {
                return null;
            }

            return $base;
        }

        return null;
    }
}
