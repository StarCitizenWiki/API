<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a lucide icon placeholder', function (): void {
    $output = Blade::render('<x-icon name="search" />');

    expect($output)->toContain('data-lucide="search"');
});
