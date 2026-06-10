<?php

declare(strict_types=1);

use App\Support\Items\ItemFieldCatalog;
use Illuminate\Support\Facades\File;

function withFakeCatalog(array $entries): array
{
    $path = storage_path('app/generated/item-fields.json');
    $original = File::exists($path) ? File::get($path) : null;

    File::put($path, json_encode($entries));

    return [
        'restore' => static function () use ($path, $original): void {
            if ($original === null) {
                File::delete($path);

                return;
            }

            File::put($path, $original);
        },
    ];
}

it('merges config columns into the catalog with first-ancestor group titles', function (): void {
    $fixture = withFakeCatalog([
        ['field' => 'mass', 'title' => 'Mass', 'type' => 'integer'],
        ['field' => 'personal_weapon.damage.dps.physical', 'title' => 'Personal Weapon Damage Dps Physical', 'type' => 'number'],
    ]);

    try {
        $catalog = new ItemFieldCatalog;

        $tableColumns = [
            ['title' => 'Name', 'field' => 'name', 'headerSort' => true, 'headerFilter' => 'input'],
            [
                'title' => 'Weapon',
                'columns' => [
                    ['title' => 'Class', 'field' => 'personal_weapon.class'],
                ],
            ],
            [
                'title' => 'DPS',
                'columns' => [
                    ['title' => 'Physical', 'field' => 'personal_weapon.damage.dps.physical', 'headerSort' => true],
                    ['title' => 'Energy', 'field' => 'personal_weapon.damage.dps.energy', 'headerSort' => true],
                ],
            ],
        ];

        $entries = $catalog->forTableBuilder($tableColumns);
        $byField = collect($entries)->keyBy('field');

        expect($byField['name']['group'])->toBe('Core')
            ->and($byField['name']['title'])->toBe('Name')
            ->and($byField['name']['sortable'])->toBeTrue()
            ->and($byField['name']['filterable'])->toBeTrue()
            ->and($byField['name']['filterType'])->toBe('input')
            ->and($byField['personal_weapon.class']['group'])->toBe('Weapon')
            ->and($byField['personal_weapon.class']['title'])->toBe('Class')
            ->and($byField['personal_weapon.damage.dps.physical']['group'])->toBe('DPS')
            ->and($byField['personal_weapon.damage.dps.physical']['title'])->toBe('Physical')
            ->and($byField['personal_weapon.damage.dps.physical']['sortable'])->toBeTrue()
            ->and($byField['personal_weapon.damage.dps.energy']['group'])->toBe('DPS')
            ->and($byField['mass']['group'])->toBe('Core')
            ->and($byField['mass']['title'])->toBe('Mass');
    } finally {
        ($fixture['restore'])();
    }
});

it('omits openapi catalog entries that are already represented by config columns', function (): void {
    $fixture = withFakeCatalog([
        ['field' => 'name', 'title' => 'Name', 'type' => 'string'],
        ['field' => 'mass', 'title' => 'Mass', 'type' => 'integer'],
        ['field' => 'suit_armor.signature.electromagnetic', 'title' => 'Suit Armor Signature Electromagnetic', 'type' => 'number'],
    ]);

    try {
        $catalog = new ItemFieldCatalog;

        $tableColumns = [
            ['title' => 'Name', 'field' => 'name', 'headerSort' => true],
            [
                'title' => 'Signature',
                'columns' => [
                    ['title' => 'EM', 'field' => 'suit_armor.signature.electromagnetic', 'headerSort' => true],
                ],
            ],
        ];

        $entries = $catalog->forTableBuilder($tableColumns);
        $signatures = collect($entries)->filter(fn (array $e): bool => $e['field'] === 'suit_armor.signature.electromagnetic');

        expect($signatures)->toHaveCount(1)
            ->and($signatures->first()['group'])->toBe('Signature')
            ->and($signatures->first()['title'])->toBe('EM')
            ->and(collect($entries)->pluck('field')->all())
            ->toContain('name')
            ->toContain('suit_armor.signature.electromagnetic');
    } finally {
        ($fixture['restore'])();
    }
});

it('uses the config column title and group instead of the openapi catalog title for the same field', function (): void {
    $fixture = withFakeCatalog([
        ['field' => 'suit_armor.signature.electromagnetic', 'title' => 'Suit Armor Signature Electromagnetic', 'type' => 'number'],
    ]);

    try {
        $catalog = new ItemFieldCatalog;

        $tableColumns = [
            [
                'title' => 'Signature',
                'columns' => [
                    ['title' => 'EM', 'field' => 'suit_armor.signature.electromagnetic', 'headerSort' => true],
                ],
            ],
        ];

        $entries = $catalog->forTableBuilder($tableColumns);
        $matches = collect($entries)->where('field', 'suit_armor.signature.electromagnetic');

        expect($matches)->toHaveCount(1)
            ->and($matches->first()['title'])->toBe('EM')
            ->and($matches->first()['group'])->toBe('Signature');
    } finally {
        ($fixture['restore'])();
    }
});

it('falls back to the openapi catalog when no config columns are supplied', function (): void {
    $fixture = withFakeCatalog([
        ['field' => 'name', 'title' => 'Name', 'type' => 'string'],
        ['field' => 'personal_weapon.damage.dps.physical', 'title' => 'Personal Weapon Damage Dps Physical', 'type' => 'number'],
    ]);

    try {
        $catalog = new ItemFieldCatalog;

        $entries = $catalog->forTableBuilder();
        $byField = collect($entries)->keyBy('field');

        expect($byField)->toHaveKeys(['name', 'personal_weapon.damage.dps.physical'])
            ->and($byField['personal_weapon.damage.dps.physical']['group'])->toBe('Personal Weapon / Damage')
            ->and($byField['name']['group'])->toBe('Core');
    } finally {
        ($fixture['restore'])();
    }
});

it('uses structural-path grouping for deep openapi fields', function (): void {
    $fixture = withFakeCatalog([
        ['field' => 'name', 'title' => 'Name', 'type' => 'string'],
        ['field' => 'shield.absorption.physical', 'title' => 'Shield Absorption Physical', 'type' => 'number'],
        ['field' => 'ammunition.bullet_electron.jump_range', 'title' => 'Ammunition Bullet Electron Jump Range', 'type' => 'number'],
        ['field' => 'distortion.max', 'title' => 'Distortion Max', 'type' => 'number'],
    ]);

    try {
        $catalog = new ItemFieldCatalog;
        $entries = $catalog->forTableBuilder();
        $byField = collect($entries)->keyBy('field');

        expect($byField['shield.absorption.physical']['group'])->toBe('Shield / Absorption')
            ->and($byField['ammunition.bullet_electron.jump_range']['group'])->toBe('Ammunition / Bullet Electron')
            ->and($byField['distortion.max']['group'])->toBe('Distortion')
            ->and($byField['name']['group'])->toBe('Core');
    } finally {
        ($fixture['restore'])();
    }
});

it('places core and manufacturer groups first in the catalog', function (): void {
    $fixture = withFakeCatalog([
        ['field' => 'name', 'title' => 'Name', 'type' => 'string'],
        ['field' => 'manufacturer.name', 'title' => 'Manufacturer Name', 'type' => 'string'],
        ['field' => 'weapon.damage', 'title' => 'Weapon Damage', 'type' => 'number'],
    ]);

    try {
        $catalog = new ItemFieldCatalog;
        $entries = $catalog->forTableBuilder();
        $groups = collect($entries)->pluck('group')->all();

        expect($groups[0])->toBe('Core')
            ->and($groups[1])->toBe('Manufacturer')
            ->and(array_slice($groups, 2))->toContain('Weapon');
    } finally {
        ($fixture['restore'])();
    }
});

it('preserves column formatter, formatterParams, and suffix from the config tree', function (): void {
    $fixture = withFakeCatalog([]);

    try {
        $catalog = new ItemFieldCatalog;

        $tableColumns = [
            [
                'title' => 'Signature',
                'columns' => [
                    [
                        'title' => 'EM',
                        'field' => 'suit_armor.signature.electromagnetic',
                        'headerSort' => true,
                        'formatter' => 'pct',
                        'formatterParams' => ['precision' => false],
                        'suffix' => '%',
                    ],
                ],
            ],
        ];

        $entries = $catalog->forTableBuilder($tableColumns);
        $entry = collect($entries)->firstWhere('field', 'suit_armor.signature.electromagnetic');

        expect($entry['group'])->toBe('Signature')
            ->and($entry['formatter'])->toBe('pct')
            ->and($entry['formatter_params'])->toBe(['precision' => false])
            ->and($entry['suffix'])->toBe('%');
    } finally {
        ($fixture['restore'])();
    }
});

it('marks the synthetic locale-suffixed sibling as selectable in the column builder', function (): void {
    $fixture = withFakeCatalog([
        ['field' => 'name', 'title' => 'Name', 'type' => 'string'],
        ['field' => 'description.en_EN', 'title' => 'Description (en_EN)', 'type' => 'string', 'columnable' => true],
        ['field' => 'description', 'title' => 'Description', 'type' => 'object', 'columnable' => false],
    ]);

    try {
        $catalog = new ItemFieldCatalog;

        $entries = $catalog->forTableBuilder();
        $byField = collect($entries)->keyBy('field');

        expect($byField['description.en_EN']['columnable'])->toBeTrue()
            ->and($byField['description.en_EN']['group'])->toBe('Core')
            ->and($byField['description.en_EN']['title'])->toBe('Description (en_EN)')
            ->and($byField['description']['columnable'])->toBeFalse();
    } finally {
        ($fixture['restore'])();
    }
});
