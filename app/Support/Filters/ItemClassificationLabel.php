<?php

declare(strict_types=1);

namespace App\Support\Filters;

use Illuminate\Support\Str;

final class ItemClassificationLabel
{
    public static function resolve(mixed $value, ?string $label): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($value) {
            'FPS.Clothing.Torso' => 'Jacket',
            'FPS.Clothing.Legs' => 'Pants',
            'FPS.Weapon.Large' => 'Large Weapon',
            'FPS.Weapon.Medium' => 'Medium Weapon',
            'FPS.Weapon.Small' => 'Small Weapon',
            'Mining.Gadget' => 'Mining Gadget',
            'Mining.Module' => 'Mining Module',
            default => Str::headline(Str::afterLast((string) $value, '.')),
        };
    }
}
