<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractTranslationResource;
use Illuminate\Http\Request;

class FlightControllerResource extends AbstractTranslationResource
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
            'scm_speed' => $this->scm_speed,
            'max_speed' => $this->max_speed,
            'pitch' => $this->pitch,
            'yaw' => $this->yaw,
            'roll' => $this->roll,
        ];
    }
}
