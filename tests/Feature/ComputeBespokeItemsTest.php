<?php

declare(strict_types=1);

use App\Jobs\Game\ComputeBespokeItems;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;

beforeEach(function (): void {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.1.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Acme',
        'code' => 'ACME',
    ]);
});

describe('bespoke class-name tokens', function (): void {
    it('marks _Colonial_ items as bespoke', function (): void {
        $item = Item::factory()->create();
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'MRCK_TALN_Colonial_S3x8',
                'type' => 'MissileLauncher',
                'is_bespoke' => false,
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => [],
                    ],
                ],
            ]);

        new ComputeBespokeItems($this->gameVersion->id)->handle();

        expect(ItemData::where('class_name', 'MRCK_TALN_Colonial_S3x8')->first()->is_bespoke)->toBeTrue();
    });

    it('marks _PDC_ items as bespoke', function (): void {
        $item = Item::factory()->create();
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'Turret_PDC_SCItem_Template',
                'type' => 'Turret',
                'is_bespoke' => false,
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => [],
                    ],
                ],
            ]);

        new ComputeBespokeItems($this->gameVersion->id)->handle();

        expect(ItemData::where('class_name', 'Turret_PDC_SCItem_Template')->first()->is_bespoke)->toBeTrue();
    });

    it('does not mark universal items as bespoke', function (): void {
        $item = Item::factory()->create();
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'class_name' => 'BEHR_LaserRepeater_S1',
                'type' => 'WeaponGun',
                'is_bespoke' => false,
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => [],
                    ],
                ],
            ]);

        new ComputeBespokeItems($this->gameVersion->id)->handle();

        expect(ItemData::where('class_name', 'BEHR_LaserRepeater_S1')->first()->is_bespoke)->toBeFalse();
    });
});
