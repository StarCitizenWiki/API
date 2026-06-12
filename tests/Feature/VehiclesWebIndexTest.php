<?php

declare(strict_types=1);

if (! function_exists('tabulatorConfigPayloadByTestId')) {
    function tabulatorConfigPayloadByTestId(string $content, string $testId): array
    {
        preg_match(
            '/<script type="application\/json" id="[^"]+-config" data-testid="'.preg_quote($testId, '/').'">(.*?)<\/script>/s',
            $content,
            $matches
        );

        expect($matches[1] ?? null)->not->toBeNull();

        return json_decode(html_entity_decode($matches[1], ENT_QUOTES), true, 512, JSON_THROW_ON_ERROR);
    }
}

it('normalizes incoming filter values into initial filters for the table', function (): void {
    $request = [
        'version' => '4.0.0-LIVE',
        'filter' => [
            'manufacturer.name' => [' RSI ', '', 'Drake'],
            'role' => ' Cargo ',
            'crew.min' => [' 2 ', ''],
            'empty' => '   ',
        ],
    ];

    $response = $this->get(route('web.vehicles.index', $request));

    $expectedEndpoint = route('vehicles.index', [
        'version' => '4.0.0-LIVE',
    ]);
    $expectedFilterOptionsEndpoint = route('vehicles.filters', [
        'version' => '4.0.0-LIVE',
    ]);
    $response->assertSuccessful()
        ->assertViewHas('initialHeaderFilter', [])
        ->assertViewHas('initialFilters', [
            ['field' => 'manufacturer.name', 'value' => 'RSI,Drake'],
            ['field' => 'role', 'value' => 'Cargo'],
            ['field' => 'crew.min', 'value' => '2'],
        ])
        ->assertSeeText('Vehicles')
        ->assertSee('for="vehicles-api-url"', false)
        ->assertSee('id="vehicles-api-url"', false)
        ->assertSee('value="'.$expectedEndpoint.'"', false)
        ->assertSee('href="'.$expectedEndpoint.'"', false)
        ->assertSeeText('API URL')
        ->assertSeeText('Open');

    $config = tabulatorConfigPayloadByTestId($response->getContent(), 'tabulator-config-vehicles-table');

    expect($config['filterOptionsEndpoint'])->toBe($expectedFilterOptionsEndpoint);
});

it('exposes no initial filters when request filters are empty', function (): void {
    $response = $this->get(route('web.vehicles.index'));

    $response->assertSuccessful()
        ->assertViewHas('initialHeaderFilter', [])
        ->assertViewHas('initialFilters', [])
        ->assertSeeText('Vehicles')
        ->assertSee('for="vehicles-api-url"', false)
        ->assertSee('id="vehicles-api-url"', false)
        ->assertSee('value="'.route('vehicles.index').'"', false)
        ->assertSee('href="'.route('vehicles.index').'"', false)
        ->assertSeeText('API URL')
        ->assertSeeText('Open');

    $config = tabulatorConfigPayloadByTestId($response->getContent(), 'tabulator-config-vehicles-table');

    expect($config['filterOptionsEndpoint'])->toBe(route('vehicles.filters'));
});
