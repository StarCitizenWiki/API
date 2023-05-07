<?php

declare(strict_types=1);

namespace App\Http\Resources\SC;

use App\Http\Resources\AbstractTranslationResource;
use App\Http\Resources\SC\Item\ItemResource;
use Illuminate\Http\Request;

class VehicleItemResource extends AbstractTranslationResource
{
    public static function validIncludes(): array
    {
        return [];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'grade' => $this->grade,
                'class' => $this->class,
            ] + (new ItemResource($this->item))->toArray($request) + [
                'updated_at' => $this->updated_at,
                'version' => $this->version,
            ];
    }
}
