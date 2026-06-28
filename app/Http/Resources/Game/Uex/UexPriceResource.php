<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Uex;

use App\Http\Resources\Game\Starmap\StarmapLocationLinkResource;
use App\Models\Game\StarmapLocationData;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'uex_price',
    title: 'UEX Price',
    description: 'Price data from UEXcorp for a commodity or vehicle at a specific terminal',
    properties: [
        new OA\Property(property: 'price_buy', type: 'number', nullable: true),
        new OA\Property(property: 'price_sell', type: 'number', nullable: true),
        new OA\Property(property: 'price_rent', type: 'number', nullable: true),
        new OA\Property(property: 'terminal_id', type: 'integer', nullable: true),
        new OA\Property(property: 'terminal_code', type: 'string', nullable: true),
        new OA\Property(property: 'terminal_name', type: 'string', nullable: true),
        new OA\Property(property: 'starmap_location', ref: '#/components/schemas/starmap_location_link', nullable: true),
        new OA\Property(property: 'date_updated', type: 'string', nullable: true),
        new OA\Property(property: 'game_version', type: 'string', nullable: true),
        new OA\Property(property: 'uex_link', description: 'Link to the UEX terminal page for this item/vehicle price', type: 'string', nullable: true),
    ],
    type: 'object'
)]
final class UexPriceResource
{
    /**
     * @param  array<string, mixed>  $price
     * @return array<string, mixed>
     */
    public static function toPriceArray(array $price, ?StarmapLocationData $locationData, ?Request $request = null): array
    {
        $locationUuid = $price['starmap_location_uuid'] ?? null;

        return [
            'price_buy' => $price['price_buy'] ?? null,
            'price_sell' => $price['price_sell'] ?? null,
            'price_rent' => $price['price_rent'] ?? null,
            'terminal_id' => $price['terminal_id'] ?? null,
            'terminal_code' => $price['terminal_code'] ?? null,
            'terminal_name' => $price['terminal_name'] ?? null,
            'starmap_location' => $locationData !== null
                ? StarmapLocationLinkResource::toLinkArray($locationData, $locationUuid, $request)
                : null,
            'date_updated' => $price['date_updated'] ?? null,
            'game_version' => $price['game_version'] ?? null,
            'uex_link' => $price['uex_link'] ?? null,
        ];
    }
}
