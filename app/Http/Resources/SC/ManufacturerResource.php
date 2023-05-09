<?php

declare(strict_types=1);

namespace App\Http\Resources\SC;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Item\ItemLinkResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Http\Resources\SC\Vehicle\VehicleItemResource;
use App\Http\Resources\SC\Vehicle\VehicleLinkResource;
use App\Http\Resources\SC\Vehicle\VehicleResource;
use Illuminate\Http\Request;

class ManufacturerResource extends AbstractBaseResource
{
    private bool $isShow = false;

    /**
     * @param bool $isShow
     */
    public function setIsShow(bool $isShow): void
    {
        $this->isShow = $isShow;
    }

    public static function validIncludes(): array
    {
        return [
            'ships',
            'vehicles',
            'items',
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'uuid' => $this->uuid,
            $this->mergeWhen($this->isShow ,[
                'ships' => VehicleLinkResource::collection($this->ships()),
                'vehicles' => VehicleLinkResource::collection($this->groundVehicles()),
                'items' => ItemLinkResource::collection($this->items()),
            ]),
        ];
    }

}
