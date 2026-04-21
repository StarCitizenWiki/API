<?php

declare(strict_types=1);

namespace App\Enums\Game;

enum CraftingBlueprintMode
{
    case None;
    case Stub;
    case Full;
}
