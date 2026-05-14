<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_part',
    title: 'Vehicle Structure Part',
    description: 'Structural hierarchy for the vehicle with damage caps.',
    properties: [
        new OA\Property(property: 'name', description: 'Raw part name from scunpacked data (e.g. LEFT_WING).', type: 'string', example: 'Nose'),
        new OA\Property(property: 'display_name', description: 'Human-readable name with positional suffix (e.g. "Wing (left)").', type: 'string', example: 'Nose'),
        new OA\Property(property: 'damage_max', description: 'Maximum damage this structural part can absorb.', type: 'number', example: 2500, nullable: true),
        new OA\Property(property: 'destruction_damage', description: 'Total damage needed to destroy this part (only set when part appears in DamageBeforeDestruction).', type: 'number', example: 2500, nullable: true),
        new OA\Property(property: 'detach_damage', description: 'Total damage needed to detach this part (only set when part appears in DamageBeforeDetach).', type: 'number', example: 600, nullable: true),
        new OA\Property(
            property: 'children',
            description: 'Nested child structural parts.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_vehicle_part'),
            nullable: true
        ),
        new OA\Property(property: 'version', description: 'Game version code for this data.', type: 'string', nullable: true),
    ],
    type: 'object'
)]
/** @param array $resource Structural part entry from vehicle JSON data */
class PartResource extends AbstractBaseResource
{
    use ResolvesGameVersion;

    /**
     * Damage limits lookup shared across all part instances in a request.
     *
     * NOT safe under long-running PHP processes :-)
     *
     * @var array<string, array{destruction_damage?: int, detach_damage?: int}>
     */
    protected static array $damageLimitsLookup = [];

    /**
     * Set the damage limits lookup for all part resources.
     *
     * @param  array<string, array{destruction_damage?: int, detach_damage?: int}>  $lookup
     */
    public static function setDamageLimitsLookup(array $lookup): void
    {
        static::$damageLimitsLookup = $lookup;
    }

    public function toArray(Request $request): array
    {
        $name = Arr::get($this->resource, 'Name');
        $limitEntry = $name !== null ? (static::$damageLimitsLookup[$name] ?? []) : [];

        return [
            'name' => $name,
            'display_name' => $this->generateDisplayName($name),
            'damage_max' => Arr::get($this->resource, 'DamageMax'),
            $this->mergeWhen(array_key_exists('destruction_damage', $limitEntry), fn () => [
                'destruction_damage' => $limitEntry['destruction_damage'],
            ]),
            $this->mergeWhen(array_key_exists('detach_damage', $limitEntry), fn () => [
                'detach_damage' => $limitEntry['detach_damage'],
            ]),
            $this->mergeWhen(Arr::has($this->resource, 'Children'), [
                'children' => self::collection(Arr::get($this->resource, 'Children', [])),
            ]),
            'version' => $this->gameVersionCode(),
        ];
    }

    /**
     * Generates a display name for a part by extracting positional prefixes.
     *
     * This method replicates the logic from v2/app/Models/SC/Vehicle/VehiclePart.php
     * to ensure consistent display name generation across API versions.
     *
     * Examples:
     * - "LEFT_WING" → "Wing (left)"
     * - "FRONT_MID_LOWER_WING" → "Wing (front mid lower)"
     * - "NOSE" → "Nose"
     * - "LEFT" → "Left" (position as entire name)
     */
    private function generateDisplayName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $cleaned = strtolower(Str::replace('_', ' ', $name));

        preg_match(
            '/((left|right|tail|top|bottom|front|mid_|lower|upper|back|rear)_?)+/',
            strtolower($name),
            $matches
        );

        if (isset($matches[0]) && $matches[0] !== strtolower($name)) {
            $partName = trim(str_replace('_', ' ', str_replace($matches[0], '', strtolower($name))));
            $position = trim(str_replace('_', ' ', $matches[0]));

            return Str::ucfirst(sprintf('%s (%s)', $partName, $position));
        }

        return Str::ucfirst($cleaned);
    }
}
