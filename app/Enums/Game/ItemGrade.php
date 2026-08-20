<?php

declare(strict_types=1);

namespace App\Enums\Game;

enum ItemGrade: int
{
    case A = 1;
    case B = 2;
    case C = 3;
    case D = 4;
    case E = 5;
    case F = 6;
    case G = 7;

    public static function labelMap(): array
    {
        $map = [];

        foreach (self::cases() as $grade) {
            $map[strtolower($grade->name)] = $grade->value;
        }

        return $map;
    }
}
