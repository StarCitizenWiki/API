<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Vehicle;

use App\Models\Api\StarCitizen\Vehicle\Component\Component;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;

class ComponentTransformer extends V1Transformer
{
    public function transform(Component $component): array
    {
        return [
            'type' => $component->type,
            'name' => $component->name,
            'mounts' => $component->pivot->mounts,
            'component_size' => $component->component_size,
            'category' => $component->category,
            'size' => $component->pivot->size,
            'details' => $component->pivot->details,
            'quantity' => $component->pivot->quantity,
            'manufacturer' => $component->manufacturer,
            'component_class' => $component->component_class,
        ];
    }
}
