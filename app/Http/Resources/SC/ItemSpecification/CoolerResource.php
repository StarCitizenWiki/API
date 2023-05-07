<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractTranslationResource;
use Illuminate\Http\Request;

class CoolerResource extends AbstractTranslationResource
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
            'cooling_rate' => $this->cooling_rate,
            'suppression_ir_factor' => $this->suppression_ir_factor,
            'suppression_heat_factor' => $this->suppression_heat_factor,
        ];
    }
}
