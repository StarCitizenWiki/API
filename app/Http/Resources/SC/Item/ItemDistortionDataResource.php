<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class ItemDistortionDataResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'decay_rate' => $this->decay_rate,
            'maximum' => $this->maximum,
            'overload_ratio' => $this->overload_ratio,
            'recovery_ratio' => $this->recovery_ratio,
            'recovery_time' => $this->recovery_time,
        ];
    }
}
