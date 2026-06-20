<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_port_item_summary',
    title: 'Equipped Port Item Summary',
    description: 'Lightweight item summary derived from raw loadout JSON. No database queries are performed. Used on index routes where full item resolution is too expensive.',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique identifier of the equipped item.', type: 'string', nullable: true),
        new OA\Property(property: 'name', description: 'Item name from loadout data.', type: 'string', nullable: true),
        new OA\Property(property: 'class_name', description: 'SC class name of the item.', type: 'string', nullable: true),
        new OA\Property(property: 'type', description: 'Item type (NOITEM_ prefix removed, before dot).', type: 'string', nullable: true),
        new OA\Property(property: 'sub_type', description: 'Item sub-type (after dot).', type: 'string', nullable: true),
        new OA\Property(property: 'size', description: 'Maximum port size as proxy for item size.', type: 'integer', nullable: true),
        new OA\Property(property: 'grade', description: 'Grade from loadout data.', type: 'integer', nullable: true),
        new OA\Property(
            property: 'manufacturer',
            description: 'Manufacturer name from loadout data.',
            properties: [
                new OA\Property(property: 'name', description: 'Manufacturer display name.', type: 'string', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'link', description: 'API URL for item detail endpoint.', type: 'string', nullable: true),
        new OA\Property(property: 'web_url', description: 'Web URL for item detail page.', type: 'string', nullable: true),
    ],
    type: 'object'
)]
class PortItemSummaryResource extends AbstractBaseResource
{
    use ResolvesGameVersion;

    /**
     * The resource is a raw port array from the loadout JSON.
     * Keys: Name, ClassName, UUID, Grade, Type, MaxSize, ManufacturerName
     */
    public function toArray(Request $request): array
    {
        $res = $this->resource;
        $uuid = $res['UUID'] ?? null;
        $type = $res['Type'] ?? '.';
        $manufacturerName = $res['ManufacturerName'] ?? null;

        // Strip NOITEM_ prefix and split type.subtype
        $cleanType = str_replace('NOITEM_', '', $type);
        [$typePart, $subTypePart] = explode('.', $cleanType, 2) + [1 => null];

        return [
            'uuid' => $uuid,
            'name' => $res['Name'] ?? null,
            'class_name' => $res['ClassName'] ?? null,
            'type' => $typePart !== '' ? $typePart : null,
            'sub_type' => $subTypePart,
            'size' => $res['MaxSize'] ?? null,
            'grade' => $res['Grade'] ?? null,
            'manufacturer' => ($manufacturerName !== null && $manufacturerName !== 'Unknown Manufacturer')
                ? ['name' => $manufacturerName]
                : null,
            'link' => is_string($uuid) && $uuid !== ''
                ? $this->urlWithVersion(route('items.show', ['identifier' => $uuid]), $request)
                : null,
            'web_url' => is_string($uuid) && $uuid !== ''
                ? $this->urlWithVersion(route('web.items.show', ['item' => $uuid]), $request)
                : null,
        ];
    }
}
