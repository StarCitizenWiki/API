<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders an api url block when configured', function (): void {
    $output = Blade::render(
        '<x-tabulator-table id="example" :config="$config" />',
        [
            'config' => [
                'endpoint' => '/api/example',
                'apiUrlTargetId' => 'example-api-url',
            ],
        ]
    );

    expect($output)
        ->toContain('API URL')
        ->toContain('href="/api/example"')
        ->toContain('/api/example');
});

it('can hide the api url block while still rendering the table mount and config', function (): void {
    $output = Blade::render(
        '<x-tabulator-table id="example" :config="$config" :show-api-url="false" />',
        [
            'config' => [
                'endpoint' => '/api/example',
                'apiUrlTargetId' => 'example-api-url',
            ],
        ]
    );

    expect($output)
        ->not->toContain('API URL')
        ->toContain('data-tabulator-id="example"')
        ->toContain('id="example-config"');
});

it('omits the api url block when no target id is configured', function (): void {
    $output = Blade::render(
        '<x-tabulator-table id="example" :config="$config" />',
        [
            'config' => [
                'endpoint' => '/api/example',
            ],
        ]
    );

    expect($output)
        ->not->toContain('API URL')
        ->toContain('data-tabulator-id="example"')
        ->toContain('id="example-config"');
});
