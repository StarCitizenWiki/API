<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\TranslationResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ship_matrix_vehicle',
    title: 'Ship Matrix Vehicle',
    description: 'Ship Matrix vehicle',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'chassis_id', type: 'integer'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'slug', type: 'string'),
        new OA\Property(
            property: 'sizes',
            properties: [
                new OA\Property(property: 'length', type: 'float'),
                new OA\Property(property: 'beam', type: 'float'),
                new OA\Property(property: 'height', type: 'float'),
            ],
            type: 'object',
            deprecated: true
        ),
        new OA\Property(
            property: 'dimension',
            properties: [
                new OA\Property(property: 'length', type: 'float'),
                new OA\Property(property: 'width', type: 'float'),
                new OA\Property(property: 'height', type: 'float'),
            ],
            type: 'object'
        ),

        new OA\Property(property: 'mass', type: 'float'),
        new OA\Property(
            property: 'cargo_capacity',
            description: 'Cargo Capacity in SCU',
            type: 'float',
            nullable: true
        ),
        new OA\Property(
            property: 'crew',
            properties: [
                new OA\Property(property: 'min', type: 'integer'),
                new OA\Property(property: 'max', type: 'integer'),
            ],
            type: 'object'
        ),
        new OA\Property(property: 'health', type: 'float'),
        new OA\Property(
            property: 'speed',
            properties: [
                new OA\Property(property: 'scm', type: 'float', nullable: true),
                new OA\Property(property: 'max', type: 'float', nullable: true),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'agility',
            properties: [
                new OA\Property(property: 'pitch', type: 'float'),
                new OA\Property(property: 'yaw', type: 'float'),
                new OA\Property(property: 'roll', type: 'float'),
                new OA\Property(
                    property: 'acceleration',
                    properties: [
                        new OA\Property(property: 'x_axis', type: 'float'),
                        new OA\Property(property: 'y_axis', type: 'float'),
                        new OA\Property(property: 'z_axis', type: 'float'),
                    ],
                    type: 'object'
                ),
            ],
            type: 'object'
        ),

        new OA\Property(
            property: 'foci',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/translation')
        ),
        new OA\Property(property: 'production_status', ref: '#/components/schemas/translation'),
        new OA\Property(property: 'production_note', ref: '#/components/schemas/translation'),
        new OA\Property(property: 'type', ref: '#/components/schemas/translation'),
        new OA\Property(property: 'description', ref: '#/components/schemas/translation'),
        new OA\Property(property: 'size', ref: '#/components/schemas/translation'),
        new OA\Property(
            property: 'msrp',
            description: 'MSRP imported from the Ship Upgrade tool.',
            type: 'float',
            nullable: true
        ),
        new OA\Property(
            property: 'pledge_url',
            description: 'Link to RSI Pledge Store',
            type: 'string',
            nullable: true
        ),
        new OA\Property(
            property: 'skus',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_sku')
        ),
        new OA\Property(
            property: 'manufacturer',
            properties: [
                new OA\Property(property: 'code', type: 'string'),
                new OA\Property(property: 'name', type: 'string'),
            ],
            type: 'object'
        ),

        new OA\Property(property: 'updated_at', type: 'string'),
        new OA\Property(
            property: 'components',
            description: 'Components imported from the Ship-Matrix',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_component'),
        ),
        new OA\Property(
            property: 'loaner',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_loaner'),
        ),
        new OA\Property(property: 'link', description: 'Link to detail endpoint', type: 'string'),
    ],
    type: 'object'
)]
class VehicleResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'components',
        ];
    }

    public function toArray(Request $request): array
    {
        $includes = collect(explode(',', $request->get('include', '')))
            ->map('trim')
            ->map('strtolower')
            ->toArray();

        $this->addMetadata('deprecated_fields', [
            'sizes' => 'Use length, width, and height properties from dimension instead',
        ]);

        return [
            'id' => $this->cig_id,
            'chassis_id' => $this->chassis_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sizes' => [
                'length' => (float) ($this->length ?? 0),
                'beam' => (float) ($this->beam ?? 0),
                'height' => (float) ($this->height ?? 0),
            ],
            'dimension' => [
                'length' => (float) ($this->length ?? 0),
                'width' => (float) ($this->beam ?? 0),
                'height' => (float) ($this->height ?? 0),
            ],
            'mass' => $this->mass,
            'cargo_capacity' => $this->cargo_capacity,
            'crew' => [
                'min' => $this->min_crew,
                'max' => $this->max_crew,
            ],

            'speed' => [
                'scm' => empty($this->scm_speed) ? null : $this->scm_speed,
                'max' => empty($this->afterburner_speed) ? null : $this->afterburner_speed,
            ],

            'agility' => [
                'pitch' => $this->pitch_max,
                'yaw' => $this->yaw_max,
                'roll' => $this->roll_max,
                'acceleration' => [
                    'x_axis' => $this->x_axis_acceleration,
                    'y_axis' => $this->y_axis_acceleration,
                    'z_axis' => $this->z_axis_acceleration,
                ],
            ],
            'foci' => $this->getFociTranslations($request),
            'production_status' => TranslationResolver::resolve($this->productionStatus, $request),
            'production_note' => TranslationResolver::resolve($this->productionNote, $request),
            'type' => TranslationResolver::resolve($this->type, $request),
            'description' => TranslationResolver::resolve($this, $request),
            'size' => TranslationResolver::resolve($this->size, $request),
            'msrp' => $this->msrp,
            $this->mergeWhen($this->pledge_url !== null, [
                'pledge_url' => sprintf('https://robertsspaceindustries.com%s', $this->pledge_url),
            ]),
            'skus' => VehicleSkuResource::collection($this->skus),

            'manufacturer' => [
                'code' => $this->manufacturer->name_short,
                'name' => $this->manufacturer->name,
            ],

            $this->mergeWhen(in_array('components', $includes, true), [
                'components' => ComponentResource::collection($this->components),
            ]),

            'loaner' => VehicleLoanerResource::collection($this->loaner),

            'link' => route('shipmatrix.vehicles.show', $this->slug),

            'updated_at' => $this->updated_at,
        ];
    }

    private function getFociTranslations(Request $request): array
    {
        /** @var Collection $foci */
        $foci = $this->foci;
        $fociTranslations = [];

        $foci->each(
            function ($vehicleFocus) use (&$fociTranslations, $request) {
                $fociTranslations[] = TranslationResolver::resolve($vehicleFocus, $request);
            }
        );

        return $fociTranslations;
    }
}
