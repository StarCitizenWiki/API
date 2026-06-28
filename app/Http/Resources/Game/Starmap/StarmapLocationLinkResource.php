<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Starmap;

use App\Models\Game\StarmapLocationData;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'starmap_location_link',
    title: 'Starmap Location Link',
    description: 'Lightweight reference to a starmap location with navigation links.',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'slug', type: 'string', nullable: true),
        new OA\Property(property: 'type_name', type: 'string', nullable: true),
        new OA\Property(property: 'parent_name', type: 'string', nullable: true),
        new OA\Property(property: 'star_system_name', type: 'string', nullable: true),
        new OA\Property(property: 'link', description: 'API URL for the starmap location', type: 'string', nullable: true),
        new OA\Property(property: 'web_url', description: 'Web URL for the starmap location', type: 'string', nullable: true),
    ],
    type: 'object'
)]
final class StarmapLocationLinkResource
{
    /**
     * @return array<string, mixed>
     */
    public static function toLinkArray(StarmapLocationData $locationData, ?string $locationUuid = null, ?Request $request = null): array
    {
        $request ??= request();
        $uuid = $locationUuid ?? $locationData->location_uuid;

        return [
            'uuid' => $uuid,
            'name' => $locationData->name,
            'slug' => $locationData->location_slug,
            'type_name' => $locationData->type_name,
            'parent_name' => $locationData->parent_name,
            'star_system_name' => $locationData->star_system_name,
            'link' => $uuid !== null
                ? route('locations.show', ['identifier' => $uuid])
                : null,
            'web_url' => $uuid !== null
                ? self::appendVersion(route('web.locations.show', ['identifier' => $uuid]), $request)
                : null,
        ];
    }

    private static function appendVersion(string $url, Request $request): string
    {
        $version = $request->query('version');

        if (! is_string($version) || $version === '') {
            return $url;
        }

        return url()->query($url, ['version' => $version]);
    }
}
