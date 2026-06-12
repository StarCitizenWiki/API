<?php

declare(strict_types=1);

it('normalizes incoming starmap location filters into initial filters for the table', function (): void {
    $request = [
        'version' => '4.1.0-LIVE',
        'filter' => [
            'type_name' => [' Station ', ''],
            'system' => ' Stan ',
            'block_travel' => [' true ', ''],
            'empty' => '   ',
        ],
    ];

    $expectedEndpoint = route('locations.index', [
        'version' => '4.1.0-LIVE',
    ]);

    $response = $this->get(route('web.locations.index', $request));

    $response->assertSuccessful()
        ->assertViewHas('initialHeaderFilter', [])
        ->assertViewHas('headerFilterOptionsMap', fn (array $map): bool => $map['system'] === 'system'
            && $map['parent.name'] === 'parent_name'
            && $map['type.classification'] === 'type_classification'
            && $map['respawn_location_type'] === 'respawn_location_type'
            && $map['jurisdiction.name'] === 'jurisdiction_name'
            && $map['affiliation.name'] === 'affiliation_name'
            && $map['amenities'] === 'amenity'
            && $map['has_resources'] === 'has_resources')
        ->assertViewHas('initialFilters', [
            ['field' => 'type_name', 'value' => 'Station'],
            ['field' => 'system', 'value' => 'Stan'],
            ['field' => 'block_travel', 'value' => 'true'],
        ])
        ->assertSee('value="'.$expectedEndpoint.'"', false)
        ->assertSee('"urlField":"web_url"', false);
});

it('exposes no initial filters when starmap location request filters are empty', function (): void {
    $response = $this->get(route('web.locations.index'));

    $response->assertSuccessful()
        ->assertViewHas('initialHeaderFilter', [])
        ->assertViewHas('initialFilters', [])
        ->assertSee('value="'.route('locations.index').'"', false);
});
