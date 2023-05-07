<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractTranslationResource;
use Illuminate\Http\Request;

class ItemPowerDataResource extends AbstractTranslationResource
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
    public function toArray($request): array
    {
        return [
            'power_base' => $this->power_base,
            'power_draw' => $this->power_draw,
            'throttleable' => $this->throttleable,
            'overclockable' => $this->overclockable,
            'overclock_threshold_min' => $this->overclock_threshold_min,
            'overclock_threshold_max' => $this->overclock_threshold_max,
            'overclock_performance' => $this->overclock_performance,
            'overpower_performance' => $this->overpower_performance,
            'power_to_em' => $this->power_to_em,
            'decay_rate_em' => $this->decay_rate_em,
        ];
    }
}
