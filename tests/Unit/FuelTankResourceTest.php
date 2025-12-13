<?php

declare(strict_types=1);

use App\Http\Resources\Game\ItemSpecification\FuelTankResource;
use Illuminate\Http\Request;

it('maps fuel tank data including network and container fields', function () {
    $payload = [
        'data' => [
            'stdItem' => [
                'ResourceContainer' => [
                    'Mass' => 0,
                    'Immutable' => false,
                    'DefaultFillFraction' => 1,
                    'Capacity' => [
                        'Value' => 4.35,
                        'Unit' => 'SStandardCargoUnit',
                        'UnitName' => 'SCU',
                        'SCU' => 4.35,
                    ],
                    'InclusiveResources' => ['f4846780-182f-49d1-a711-f08c0172735e'],
                    'ExclusiveResources' => ['00000000-0000-0000-0000-000000000000'],
                    'InclusiveGroups' => ['9379b129-8688-4777-9f65-9dc09ba935ee'],
                    'ExclusiveGroups' => ['00000000-0000-0000-0000-000000000000'],
                    'DefaultComposition' => [
                        [
                            'Entry' => 'f4846780-182f-49d1-a711-f08c0172735e',
                            'Weight' => 1,
                        ],
                    ],
                ],
            ],
            'Raw' => [
                'Entity' => [
                    'Components' => [
                        'ItemResourceComponentParams' => [
                            'isResourceNetworked' => 1,
                            'isRelay' => 0,
                            'isConnectedToRoom' => 0,
                            'wirelessConnection' => 0,
                            'defaultPriority' => 100,
                            'states' => [
                                'ItemResourceState' => [
                                    'name' => 'Online',
                                    'deltas' => [
                                        'ItemResourceDeltaStorage' => [
                                            'generation' => [
                                                'resourceAmountPerSecond' => [
                                                    'SStandardResourceUnit' => [
                                                        'standardResourceUnits' => 10,
                                                    ],
                                                ],
                                            ],
                                            'consumption' => [
                                                'resourceAmountPerSecond' => [
                                                    'SStandardResourceUnit' => [
                                                        'standardResourceUnits' => 10,
                                                    ],
                                                ],
                                            ],
                                            'discharge' => 0,
                                            'consumptionComposition' => ['values' => []],
                                            'dynamicResourceOverride' => [],
                                            'transferModifiers' => [],
                                        ],
                                    ],
                                    'linkedInteractionStates' => [],
                                    'signatureParams' => [
                                        'EMSignature' => ['nominalSignature' => 0, 'decayRate' => 0.15],
                                        'IRSignature' => ['nominalSignature' => 0, 'decayRate' => 0.15],
                                    ],
                                    'rangeParams' => [],
                                ],
                            ],
                            'filterParams' => [],
                            'controlParameters' => [],
                            'controlBlocks' => [],
                            'functionalityModifiers' => [],
                            'rangeParams' => [],
                            'powerPlantOverride' => [],
                        ],
                        'SCItemFuelTankParams' => [
                            'openState' => 'null',
                            'closedState' => 'null',
                            'pumpingState' => 'null',
                            'fuelFlowLoopStartAudioTrigger' => ['audioTrigger' => ''],
                            'fuelFlowLoopStopAudioTrigger' => ['audioTrigger' => ''],
                            'fuelFillDrainRateAudioRtpc' => ['rtpc' => ''],
                            'fuelFillLevelAudioRtpc' => ['rtpc' => ''],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $resource = new FuelTankResource($payload);

    $data = $resource->toArray(new Request);

    expect($data['fill_rate'])->toBe(10)
        ->and($data['drain_rate'])->toBe(10)
        ->and($data['discharge_rate'])->toBe(0)
        ->and($data['capacity']['scu'])->toBe(4.35)
        ->and($data['default_fill_fraction'])->toBe(1)
        ->and($data['inclusive_resources'])->toContain('f4846780-182f-49d1-a711-f08c0172735e')
        ->and($data['resource_network']['default_priority'])->toBe(100)
        ->and($data['tank']['open_state'])->toBe('null');
});
