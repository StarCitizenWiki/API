<?php

declare(strict_types=1);

namespace App\Support\Filters;

use Illuminate\Support\Str;

final class ItemFilterLabel
{
    public static function resolveType(mixed $value, ?string $label): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($value) {
            'Char_Armor_Arms' => 'Arms (Armor)',
            'Char_Armor_Backpack' => 'Backpack (Armor)',
            'Char_Armor_Helmet' => 'Helmet (Armor)',
            'Char_Armor_Legs' => 'Legs (Armor)',
            'Char_Armor_Torso' => 'Torso (Armor)',
            'Char_Armor_Undersuit' => 'Undersuit (Armor)',
            'Char_Clothing_Backpack' => 'Backpack',
            'Char_Clothing_Feet' => 'Shoes',
            'Char_Clothing_Hands' => 'Gloves',
            'Char_Clothing_Hat' => 'Hat',
            'Char_Clothing_Legs' => 'Legs',
            'Char_Clothing_Torso_0' => 'Shirt',
            'Char_Clothing_Torso_1' => 'Jacket',
            'Char_Clothing_Torso_2' => 'Outerwear',
            'WeaponAttachment' => 'Weapon Attachment',
            'WeaponPersonal' => 'FPS Weapon',
            default => Str::headline(str_replace(['.', '_'], ' ', $value)),
        };
    }

    public static function resolveClassification(mixed $value, ?string $label): ?string
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

    public static function resolveSubType(mixed $value, ?string $label): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($value) {
            'UNDEFINED' => 'Undefined',
            default => Str::headline(str_replace(['.', '_'], ' ', $value)),
        };
    }
}
