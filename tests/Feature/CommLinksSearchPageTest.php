<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the comm-link search page', function (): void {
    $response = $this->get(route('web.comm-links.search'));

    $response->assertOk()
        ->assertViewIs('comm-links.search');
});
