<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\WeaponPersonal\Attachment;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class WeaponPersonalAttachmentsTransformer extends AbstractCommodityTransformer
{
    protected array $availableIncludes = [
        'shops',
    ];

    /**
     * @param Attachment $port
     *
     * @return array
     */
    public function transform(Attachment $port): array
    {
        $data = [
            'uuid' => $port->item->uuid,
            'name' => $port->attachment_name,
            'description' => $this->getTranslation($port),
            'type' => $port->type,
            'sub_type' => $port->item->sub_type,
            'position' => $port->position,
            'size' => $port->size,
            'manufacturer' => $port->item->manufacturer,
            'grade' => $port->grade,
            'volume' => [
                'width' => $port->item->volume->width,
                'height' => $port->item->volume->height,
                'length' => $port->item->volume->length,
                'volume' => $port->item->volume->volume,
            ],
            'updated_at' => $port->updated_at,
            'version' => $port->version,
        ];

        switch ($port->type) {
            case 'Magazine':
                $data['capacity'] = $port->magazine->capacity ?? null;
                break;

            case 'Scope':
                $data += [
                    'magnification' => $port->optics->magnification ?? null,
                    'optic_type' => $port->optics->type ?? null,
                ];
                break;
        }

        return $data;
    }
}
