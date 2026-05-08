<?php

declare(strict_types=1);

namespace App\Http\Resources\Game;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'version_diff_entry',
    title: 'Version Diff Entry',
    properties: [
        new OA\Property(property: 'entity_type', type: 'string', example: 'item'),
        new OA\Property(property: 'entity_id', type: 'integer', example: 42),
        new OA\Property(property: 'change_type', type: 'string', example: 'modified'),
        new OA\Property(property: 'column_changes', type: 'object', example: '{"name": {"old": "Old Name", "new": "New Name"}}'),
        new OA\Property(property: 'data_changes', type: 'object', example: '{"stdItem.Mass": {"old": 5, "new": 7}}'),
    ],
    type: 'object'
)]
class VersionDiffResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'change_type' => $this->change_type,
            'column_changes' => $this->column_changes,
            'data_changes' => $this->data_changes,
        ];
    }
}
