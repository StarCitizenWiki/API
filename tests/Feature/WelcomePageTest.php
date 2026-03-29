<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

it('renders the welcome page categories for guests', function (): void {
    $response = $this->get(route('home'));

    $response->assertSuccessful()
        ->assertSee([
            'Search items',
            'Home',
            'Comm-Link',
            'Galactapedia',
            'Universe',
            'Statistics',
            'Ship-Matrix',
            'Starmap',
            'Explore',
            'Comm-Links',
            'Vehicles',
            'All Items',
            'Stats',
            'Systems',
            'Celestial Objects',
            'Api Documentation',
            'Source Code',
        ]);

    $response->assertSee('name="filter[name]"', false);

    $response->assertDontSee('Admin');
});

it('shows the admin link for authorized users when the route exists', function (): void {
    if (! Route::has('admin.dashboard')) {
        $this->markTestSkipped('Admin dashboard route not registered.');
    }

    $user = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($user)->get(route('home'))->assertSuccessful();

    $response->assertSee('Admin');
});
