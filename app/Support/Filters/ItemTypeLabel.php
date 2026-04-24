<?php

declare(strict_types=1);

namespace App\Support\Filters;

use Illuminate\Support\Str;

final class ItemTypeLabel
{
    public static function resolve(mixed $value, ?string $label): ?string
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
            'WeaponAttachment' => 'Weapon Attachment',
            'WeaponPersonal' => 'FPS Weapon',
            default => Str::headline(str_replace(['.', '_'], ' ', $value)),
        };
    }
}
