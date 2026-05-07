<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('API routes', function () {
    it('sets Cache-Tag header on API vehicle index', function () {
        GameVersion::factory()->create(['is_default' => true]);

        $this->getJson('/api/vehicles')
            ->assertSuccessful()
            ->assertHeader('Cache-Tag', 'api, api-vehicles');
    });

    it('sets Cache-Tag header on API comm-links index', function () {
        $this->getJson('/api/comm-links')
            ->assertSuccessful()
            ->assertHeader('Cache-Tag', 'api, api-comm-links');
    });

    it('sets Cache-Tag header on API galactapedia index', function () {
        $this->getJson('/api/galactapedia')
            ->assertSuccessful()
            ->assertHeader('Cache-Tag', 'api, api-galactapedia');
    });

    it('sets Cache-Tag header on API items index', function () {
        GameVersion::factory()->create(['is_default' => true]);

        $this->getJson('/api/items')
            ->assertSuccessful()
            ->assertHeader('Cache-Tag', 'api, api-items');
    });

    it('does not set Cache-Tag on POST search endpoints', function () {
        GameVersion::factory()->create(['is_default' => true]);

        $this->postJson('/api/vehicles/search', ['query' => 'test'])
            ->assertHeaderMissing('Cache-Tag');
    });

    it('does not set Cache-Tag on closure routes', function () {
        $this->getJson('/api/openapi')
            ->assertHeaderMissing('Cache-Tag');
    });
});

describe('Web routes', function () {
    it('sets Cache-Tag header on web vehicle index', function () {
        GameVersion::factory()->create(['is_default' => true]);

        $this->get('/vehicles')
            ->assertSuccessful()
            ->assertHeader('Cache-Tag', 'web, web-vehicles');
    });

    it('sets Cache-Tag header on web comm-links index', function () {
        $this->get('/comm-links')
            ->assertSuccessful()
            ->assertHeader('Cache-Tag', 'web, web-comm-links');
    });

    it('sets Cache-Tag header on web galactapedia index', function () {
        $this->get('/galactapedia')
            ->assertSuccessful()
            ->assertHeader('Cache-Tag', 'web, web-galactapedia');
    });

    it('sets Cache-Tag header on web blueprints index', function () {
        GameVersion::factory()->create(['is_default' => true]);

        $this->get('/blueprints')
            ->assertSuccessful()
            ->assertHeader('Cache-Tag', 'web, web-blueprints');
    });
});

describe('Uncached routes', function () {
    it('does not set Cache-Tag on admin dashboard', function () {
        $this->get('/admin')
            ->assertRedirect();
    });
});
