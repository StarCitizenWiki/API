<?php

declare(strict_types=1);

namespace App\Enums\Game;

enum ResourceKind: string
{
    case Mineable = 'mineable';
    case Harvestable = 'harvestable';
    case Salvage = 'salvage';
    case Loot = 'loot';
    case Remains = 'remains';
    case Fossil = 'fossil';

    public static function fromRawKind(string $kind): self
    {
        return match ($kind) {
            'mineable' => self::Mineable,
            'harvestable', 'cave_harvestable' => self::Harvestable,
            'salvageable' => self::Salvage,
            default => self::Mineable,
        };
    }

    public static function fromGroupName(string $groupName): self
    {
        $lower = mb_strtolower($groupName);

        return match (true) {
            str_contains($lower, 'mineable') => self::Mineable,
            str_contains($lower, 'harvestable'),
            str_contains($lower, 'havestable'),
            str_contains($lower, 'plant') => self::Harvestable,
            str_contains($lower, 'salvage') => self::Salvage,
            str_contains($lower, 'remains') => self::Remains,
            str_contains($lower, 'loot') => self::Loot,
            str_contains($lower, 'fossil') => self::Fossil,
            default => self::Mineable,
        };
    }
}
