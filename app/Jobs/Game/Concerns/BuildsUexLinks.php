<?php

declare(strict_types=1);

namespace App\Jobs\Game\Concerns;

trait BuildsUexLinks
{
    private static function buildItemLink(?string $itemName): ?string
    {
        if ($itemName === null || $itemName === '') {
            return null;
        }

        $slug = strtolower(str_replace(' ', '-', $itemName));

        return "https://uexcorp.space/items/info?name={$slug}&tab=about";
    }

    private static function buildVehicleLink(int $terminalId, string $priceField): string
    {
        $tab = $priceField === 'price_rent' ? 'in_game_rent' : 'in_game_sell';

        return "https://uexcorp.space/vehicles/home/list/{$tab}/?id_terminal={$terminalId}";
    }

    private static function buildCommodityLink(string $commodityName): string
    {
        $slug = strtolower(str_replace(' ', '-', $commodityName));

        return "https://uexcorp.space/commodities/info/name/{$slug}/tab/overview/";
    }
}
