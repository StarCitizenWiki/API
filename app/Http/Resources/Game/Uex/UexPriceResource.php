<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Uex;

use App\Http\Resources\AbstractBaseResource;
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
class UexPriceResource extends AbstractBaseResource
{
    public function __construct(
        $resource,
        private readonly ?StarmapLocationData $locationData = null,
    ) {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $res = $this->resource;
        $locationUuid = $res['starmap_location_uuid'] ?? null;

        return [
            'price_buy' => $res['price_buy'] ?? null,
            'price_sell' => $res['price_sell'] ?? null,
            'price_rent' => $res['price_rent'] ?? null,
            'terminal_id' => $res['terminal_id'] ?? null,
            'terminal_code' => $res['terminal_code'] ?? null,
            'terminal_name' => $res['terminal_name'] ?? null,
            'starmap_location' => $this->locationData !== null
                ? new StarmapLocationLinkResource($this->locationData, $locationUuid)->resolve($request)
                : null,
            'date_updated' => $res['date_updated'] ?? null,
            'game_version' => $res['game_version'] ?? null,
            'uex_link' => $res['uex_link'] ?? null,
        ];
    }
}
