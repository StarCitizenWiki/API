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
        ->toContain('example-api-url')
        ->toContain('data-api-url-open="example-api-url"')
        ->toContain('/api/example');
});

it('can hide the api url block', function (): void {
    $output = Blade::render(
        '<x-tabulator-table id="example" :config="$config" :show-api-url="false" />',
        [
            'config' => [
                'endpoint' => '/api/example',
                'apiUrlTargetId' => 'example-api-url',
            ],
        ]
    );

    expect($output)->not->toContain('data-api-url-open="example-api-url"');
});
