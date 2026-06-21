<?php

declare(strict_types=1);

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\ItemData;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;

beforeEach(function (): void {
    $this->gameVersion = createDefaultGameVersion();
});

/**
 * Default HaulingOrders entry shape with zeroed-out optional keys;
 * tests override only the fields relevant to their case.
 */
function makeHaulingOrder(array $overrides = []): array
{
    return array_merge([
        'Kind' => 'Resource',
        'Name' => 'Default',
        'UUID' => null,
        'MinScu' => 0,
        'MaxScu' => 0,
        'MinAmount' => 0,
        'MaxAmount' => 0,
        'MaxContainerSize' => -1,
        'Items' => [],
    ], $overrides);
}

describe('regular orders', function (): void {
    it('renders hauling section with commodity orders', function (): void {
        $commodity = Commodity::factory()->create(['name' => 'Laranite']);
        $mission = Mission::factory()->create();
        MissionData::factory()
            ->forVersion($this->gameVersion)
            ->forMission($mission)
            ->create([
                'title' => 'Deliver Laranite',
                'reward_scope' => 'Hauling',
                'data' => [
                    'HaulingOrders' => [
                        makeHaulingOrder([
                            'Kind' => 'Resource',
                            'Name' => 'Laranite',
                            'UUID' => $commodity->uuid,
                            'MinScu' => 10,
                            'MaxScu' => 24,
                        ]),
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('Hauling Orders')
            ->assertSee('Laranite')
            ->assertSee('Commodity')
            ->assertSee('10 - 24 SCU');
    });

    it('renders entity order with amount metric', function (): void {
        $item = ItemData::factory()->create(['name' => 'Wikelo Favor']);
        $mission = Mission::factory()->create();
        MissionData::factory()
            ->forVersion($this->gameVersion)
            ->forMission($mission)
            ->create([
                'title' => 'Your Best Shot',
                'reward_scope' => 'Hauling',
                'data' => [
                    'HaulingOrders' => [
                        makeHaulingOrder([
                            'Kind' => 'Entity',
                            'Name' => 'Wikelo Favor',
                            'UUID' => $item->uuid,
                            'MinAmount' => 1,
                            'MaxAmount' => 1,
                        ]),
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('Entity')
            ->assertSee('Wikelo Favor')
            ->assertSee('1 ×');
    });

    it('renders entity group without link and shows sub-items', function (): void {
        $item = ItemData::factory()->create(['name' => 'Medical Gown']);
        $mission = Mission::factory()->create();
        MissionData::factory()
            ->forVersion($this->gameVersion)
            ->forMission($mission)
            ->create([
                'title' => 'Deliver Components',
                'reward_scope' => 'Hauling',
                'data' => [
                    'HaulingOrders' => [
                        makeHaulingOrder([
                            'Kind' => 'Entities',
                            'Name' => 'Power Plant, Industrial Grade (S2)',
                            'UUID' => $item->uuid,
                            'MinAmount' => 3,
                            'MaxAmount' => 3,
                            'Items' => [
                                ['Name' => 'Diligence', 'UUID' => $item->uuid],
                                ['Name' => 'Genoa', 'UUID' => $item->uuid],
                            ],
                        ]),
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('Entity Group')
            ->assertSee('Power Plant, Industrial Grade (S2)')
            ->assertSee('Diligence')
            ->assertSee('Genoa')
            ->assertSee('3 ×');
    });

    it('renders mission item order', function (): void {
        $item = ItemData::factory()->create(['name' => 'Flight Recorder']);
        $mission = Mission::factory()->create();
        MissionData::factory()
            ->forVersion($this->gameVersion)
            ->forMission($mission)
            ->create([
                'title' => 'Black Box Recovery',
                'reward_scope' => 'Investigation',
                'data' => [
                    'HaulingOrders' => [
                        makeHaulingOrder([
                            'Kind' => 'MissionItem',
                            'Name' => 'Flight Recorder',
                            'UUID' => $item->uuid,
                            'MinAmount' => 1,
                            'MaxAmount' => 1,
                            'MaxContainerSize' => 0,
                        ]),
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('Item')
            ->assertSee('Flight Recorder');
    });

    it('renders multiple orders in a list with separators', function (): void {
        $c1 = Commodity::factory()->create(['name' => 'Quartz']);
        $c2 = Commodity::factory()->create(['name' => 'Copper']);
        $mission = Mission::factory()->create();
        MissionData::factory()
            ->forVersion($this->gameVersion)
            ->forMission($mission)
            ->create([
                'title' => 'Multi Commodity',
                'reward_scope' => 'Hauling',
                'data' => [
                    'HaulingOrders' => [
                        makeHaulingOrder(['Name' => 'Quartz', 'UUID' => $c1->uuid, 'MinScu' => 21, 'MaxScu' => 21]),
                        makeHaulingOrder(['Name' => 'Copper', 'UUID' => $c2->uuid, 'MinScu' => 18, 'MaxScu' => 18]),
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('Quartz')
            ->assertSee('Copper')
            ->assertSee('21 SCU')
            ->assertSee('18 SCU');
    });
});

describe('choice orders', function (): void {
    it('renders choice as a collapsible section', function (): void {
        $c1 = Commodity::factory()->create(['name' => 'Construction Rubble']);
        $c2 = Commodity::factory()->create(['name' => 'Construction Pieces']);
        $mission = Mission::factory()->create();
        MissionData::factory()
            ->forVersion($this->gameVersion)
            ->forMission($mission)
            ->create([
                'title' => 'Choice Delivery',
                'reward_scope' => 'Hauling',
                'data' => [
                    'HaulingOrders' => [
                        [
                            'Kind' => 'Or',
                            'OrOptions' => [
                                [makeHaulingOrder(['Name' => 'Construction Rubble', 'UUID' => $c1->uuid, 'MinScu' => 10, 'MaxScu' => 10])],
                                [makeHaulingOrder(['Name' => 'Construction Pieces', 'UUID' => $c2->uuid, 'MinScu' => 15, 'MaxScu' => 15])],
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('haul one of')
            ->assertSee('Construction Rubble')
            ->assertSee('Construction Pieces')
            ->assertSee('10 SCU')
            ->assertSee('15 SCU');
    });

    it('renders mixed regular and choice orders', function (): void {
        $c1 = Commodity::factory()->create(['name' => 'Recycled Material Composite']);
        $c2 = Commodity::factory()->create(['name' => 'Construction Rubble']);
        $c3 = Commodity::factory()->create(['name' => 'Construction Pieces']);
        $mission = Mission::factory()->create();
        MissionData::factory()
            ->forVersion($this->gameVersion)
            ->forMission($mission)
            ->create([
                'title' => 'Salvager Small',
                'reward_scope' => 'Salvage',
                'data' => [
                    'HaulingOrders' => [
                        makeHaulingOrder(['Name' => 'Recycled Material Composite', 'UUID' => $c1->uuid, 'MinScu' => 5, 'MaxScu' => 5]),
                        [
                            'Kind' => 'Or',
                            'OrOptions' => [
                                [makeHaulingOrder(['Name' => 'Construction Rubble', 'UUID' => $c2->uuid, 'MinScu' => 10, 'MaxScu' => 10])],
                                [makeHaulingOrder(['Name' => 'Construction Pieces', 'UUID' => $c3->uuid, 'MinScu' => 15, 'MaxScu' => 15])],
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('Recycled Material Composite')
            ->assertSee('Construction Rubble')
            ->assertSee('Construction Pieces')
            ->assertSee('Commodity')
            ->assertSee('haul one of');
    });
});

describe('exact value display', function (): void {
    it('shows exact SCU when one bound is zero', function (): void {
        $commodity = Commodity::factory()->create(['name' => 'E\'tam']);
        $mission = Mission::factory()->create();
        MissionData::factory()
            ->forVersion($this->gameVersion)
            ->forMission($mission)
            ->create([
                'title' => 'Cargo Haul',
                'reward_scope' => 'Hauling',
                'data' => [
                    'HaulingOrders' => [
                        makeHaulingOrder(['Name' => "E'tam", 'UUID' => $commodity->uuid, 'MinScu' => 4, 'MaxScu' => 0, 'MaxContainerSize' => 2]),
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('4 SCU')
            ->assertDontSee('≤ 4 SCU');
    });

    it('shows exact amount when one bound is zero', function (): void {
        $item = ItemData::factory()->create(['name' => 'Data Pad']);
        $mission = Mission::factory()->create();
        MissionData::factory()
            ->forVersion($this->gameVersion)
            ->forMission($mission)
            ->create([
                'title' => 'Courier Run',
                'reward_scope' => 'Hauling',
                'data' => [
                    'HaulingOrders' => [
                        makeHaulingOrder(['Kind' => 'Entity', 'Name' => 'Data Pad', 'UUID' => $item->uuid, 'MinAmount' => 3, 'MaxAmount' => 0]),
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('3 ×')
            ->assertDontSee('≤ 3 ×');
    });

    it('shows range when both bounds differ', function (): void {
        $commodity = Commodity::factory()->create(['name' => 'Aphorite']);
        $mission = Mission::factory()->create();
        MissionData::factory()
            ->forVersion($this->gameVersion)
            ->forMission($mission)
            ->create([
                'title' => 'Mining Haul',
                'reward_scope' => 'Hauling',
                'data' => [
                    'HaulingOrders' => [
                        makeHaulingOrder(['Name' => 'Aphorite', 'UUID' => $commodity->uuid, 'MinScu' => 9, 'MaxScu' => 16]),
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('9 - 16 SCU');
    });
});

describe('container size', function (): void {
    it('shows container size badge when positive', function (): void {
        $commodity = Commodity::factory()->create(['name' => 'E\'tam']);
        $mission = Mission::factory()->create();
        MissionData::factory()
            ->forVersion($this->gameVersion)
            ->forMission($mission)
            ->create([
                'title' => 'Cargo Haul',
                'reward_scope' => 'Hauling',
                'data' => [
                    'HaulingOrders' => [
                        makeHaulingOrder(['Name' => "E'tam", 'UUID' => $commodity->uuid, 'MinScu' => 4, 'MaxScu' => 4, 'MaxContainerSize' => 2]),
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('2 SCU container');
    });

    it('hides container size when negative or zero', function (): void {
        $commodity = Commodity::factory()->create(['name' => 'Quartz']);
        $mission = Mission::factory()->create();
        MissionData::factory()
            ->forVersion($this->gameVersion)
            ->forMission($mission)
            ->create([
                'title' => 'Mining Run',
                'reward_scope' => 'Hauling',
                'data' => [
                    'HaulingOrders' => [
                        makeHaulingOrder(['Name' => 'Quartz', 'UUID' => $commodity->uuid, 'MinScu' => 2, 'MaxScu' => 2]),
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertDontSee('SCU container');
    });
});

describe('absence', function (): void {
    it('does not render hauling section when there are no orders', function (): void {
        $mission = Mission::factory()->create();
        MissionData::factory()
            ->forVersion($this->gameVersion)
            ->forMission($mission)
            ->create([
                'title' => 'No Hauling',
                'reward_scope' => 'Hauling',
                'data' => [],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertDontSee('Hauling Orders');
    });
});
