<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the comm-link search page', function (): void {
    $response = $this->get(route('web.comm-links.search'));

    $response->assertOk()
        ->assertViewIs('comm-links.search')
        ->assertSee('Comm-Link Search')
        ->assertSee('Search results open in the matching index view.')
        ->assertSee('Search title')
        ->assertSee('Search media URL')
        ->assertSee('Search by image');
});
