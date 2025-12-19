<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'barrel_attachment',
    title: 'Barrel Attachment',
    description: 'Barrel attachments such as suppressors, compensators and flash hiders sourced from Item.stdItem.WeaponAttachment.',
    properties: [
        new OA\Property(property: 'attachment_point', type: 'string', example: 'Barrel', nullable: true),
        new OA\Property(property: 'type', description: 'Use "attachment_point".', type: 'string', nullable: true, deprecated: true),
    ],
    type: 'object'
)]
class BarrelAttachmentResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);

        $weaponAttachment = Arr::get($stdItem, 'WeaponAttachment', []);

        return [
            'attachment_point' => Arr::get($weaponAttachment, 'AttachmentPoint'),
            'type' => Arr::get($weaponAttachment, 'AttachmentPoint'),
        ];
    }
}
