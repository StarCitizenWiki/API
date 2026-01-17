<?php

declare(strict_types=1);

use App\Services\ApiJsonRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

it('renders the vehicles index mapping', function (): void {
    mock(ApiJsonRequest::class)
        ->shouldReceive('request')
        ->twice()
        ->andReturn(
            ['data' => []],
            ['filters' => []]
        );

    $response = $this->get(route('web.vehicles.index'));

    $response->assertOk()
        ->assertViewIs('vehicles.index')
        ->assertSee('Column source map');
});
