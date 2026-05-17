<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Uex;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Starmap\StarmapLocationLinkResource;
use App\Models\Game\StarmapLocationData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        $locationUuid = Arr::get($this->resource, 'starmap_location_uuid');

        return [
            'price_buy' => Arr::get($this->resource, 'price_buy'),
            'price_sell' => Arr::get($this->resource, 'price_sell'),
            'price_rent' => Arr::get($this->resource, 'price_rent'),
            'terminal_id' => Arr::get($this->resource, 'terminal_id'),
            'terminal_code' => Arr::get($this->resource, 'terminal_code'),
            'terminal_name' => Arr::get($this->resource, 'terminal_name'),
            'starmap_location' => $this->locationData !== null
                ? new StarmapLocationLinkResource($this->locationData, $locationUuid)->resolve($request)
                : null,
            'date_updated' => Arr::get($this->resource, 'date_updated'),
            'game_version' => Arr::get($this->resource, 'game_version'),
            'uex_link' => Arr::get($this->resource, 'uex_link'),
        ];
    }
}
