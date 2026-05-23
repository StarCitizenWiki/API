<?php

declare(strict_types=1);

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\ItemData;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;

beforeEach(function (): void {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

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
                        [
                            'Kind' => 'Resource',
                            'Name' => 'Laranite',
                            'UUID' => $commodity->uuid,
                            'MinScu' => 10,
                            'MaxScu' => 24,
                            'MinAmount' => 0,
                            'MaxAmount' => 0,
                            'MaxContainerSize' => -1,
                            'Items' => [],
                        ],
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('Hauling Orders')
            ->assertSee('Laranite')
            ->assertSee('(Commodity)')
            ->assertSee('10 - 24 SCU')
            ->assertSee('badge-info', escape: false);
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
                        [
                            'Kind' => 'Entity',
                            'Name' => 'Wikelo Favor',
                            'UUID' => $item->uuid,
                            'MinScu' => 0,
                            'MaxScu' => 0,
                            'MinAmount' => 1,
                            'MaxAmount' => 1,
                            'MaxContainerSize' => -1,
                            'Items' => [],
                        ],
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('Entity')
            ->assertSee('Wikelo Favor')
            ->assertSee('1 ×')
            ->assertSee('badge-warning', escape: false);
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
                        [
                            'Kind' => 'Entities',
                            'Name' => 'Power Plant, Industrial Grade (S2)',
                            'UUID' => $item->uuid,
                            'MinScu' => 0,
                            'MaxScu' => 0,
                            'MinAmount' => 3,
                            'MaxAmount' => 3,
                            'MaxContainerSize' => -1,
                            'Items' => [
                                ['Name' => 'Diligence', 'UUID' => $item->uuid],
                                ['Name' => 'Genoa', 'UUID' => $item->uuid],
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('Entity Group')
            ->assertSee('Power Plant, Industrial Grade (S2)')
            ->assertSee('Diligence')
            ->assertSee('Genoa')
            ->assertSee('3 ×')
            ->assertSee('badge-warning', escape: false);
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
                        [
                            'Kind' => 'MissionItem',
                            'Name' => 'Flight Recorder',
                            'UUID' => $item->uuid,
                            'MinScu' => 0,
                            'MaxScu' => 0,
                            'MinAmount' => 1,
                            'MaxAmount' => 1,
                            'MaxContainerSize' => 0,
                            'Items' => [],
                        ],
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
                        [
                            'Kind' => 'Resource',
                            'Name' => 'Quartz',
                            'UUID' => $c1->uuid,
                            'MinScu' => 21,
                            'MaxScu' => 21,
                            'MinAmount' => 0,
                            'MaxAmount' => 0,
                            'MaxContainerSize' => -1,
                            'Items' => [],
                        ],
                        [
                            'Kind' => 'Resource',
                            'Name' => 'Copper',
                            'UUID' => $c2->uuid,
                            'MinScu' => 18,
                            'MaxScu' => 18,
                            'MinAmount' => 0,
                            'MaxAmount' => 0,
                            'MaxContainerSize' => -1,
                            'Items' => [],
                        ],
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('Quartz')
            ->assertSee('Copper')
            ->assertSee('21 SCU')
            ->assertSee('18 SCU')
            ->assertSee('badge-info', escape: false)
            ->assertSee('class="list"', escape: false)
            ->assertSee('class="list-row"', escape: false);
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
                                [[
                                    'Kind' => 'Resource',
                                    'Name' => 'Construction Rubble',
                                    'UUID' => $c1->uuid,
                                    'MinScu' => 10,
                                    'MaxScu' => 10,
                                    'MinAmount' => 0,
                                    'MaxAmount' => 0,
                                    'MaxContainerSize' => -1,
                                    'Items' => [],
                                ]],
                                [[
                                    'Kind' => 'Resource',
                                    'Name' => 'Construction Pieces',
                                    'UUID' => $c2->uuid,
                                    'MinScu' => 15,
                                    'MaxScu' => 15,
                                    'MinAmount' => 0,
                                    'MaxAmount' => 0,
                                    'MaxContainerSize' => -1,
                                    'Items' => [],
                                ]],
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->get("/missions/{$mission->slug}");

        $response->assertSuccessful()
            ->assertSee('Choice')
            ->assertSee('Deliver one of the following')
            ->assertSee('Construction Rubble')
            ->assertSee('Construction Pieces')
            ->assertSee('10 SCU')
            ->assertSee('15 SCU')
            ->assertSee('collapse', escape: false)
            ->assertSee('badge-info', escape: false);
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
                        [
                            'Kind' => 'Resource',
                            'Name' => 'Recycled Material Composite',
                            'UUID' => $c1->uuid,
                            'MinScu' => 5,
                            'MaxScu' => 5,
                            'MinAmount' => 0,
                            'MaxAmount' => 0,
                            'MaxContainerSize' => -1,
                            'Items' => [],
                        ],
                        [
                            'Kind' => 'Or',
                            'OrOptions' => [
                                [[
                                    'Kind' => 'Resource',
                                    'Name' => 'Construction Rubble',
                                    'UUID' => $c2->uuid,
                                    'MinScu' => 10,
                                    'MaxScu' => 10,
                                    'MinAmount' => 0,
                                    'MaxAmount' => 0,
                                    'MaxContainerSize' => -1,
                                    'Items' => [],
                                ]],
                                [[
                                    'Kind' => 'Resource',
                                    'Name' => 'Construction Pieces',
                                    'UUID' => $c3->uuid,
                                    'MinScu' => 15,
                                    'MaxScu' => 15,
                                    'MinAmount' => 0,
                                    'MaxAmount' => 0,
                                    'MaxContainerSize' => -1,
                                    'Items' => [],
                                ]],
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
            ->assertSee('Choice');
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
