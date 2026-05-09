<?php

declare(strict_types=1);

namespace App\Support\Game;

use App\Http\Resources\Game\Vehicle\Concerns\CategorizesEquipmentType;

/**
 * Thin wrapper that exposes the canonical equipment category order for use in Blade views.
 *
 * Usage in Blade: App\Support\Game\EquipmentCategoryOrder::all()
 */
final class EquipmentCategoryOrder
{
    use CategorizesEquipmentType;

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return self::categoryOrder();
    }
}
