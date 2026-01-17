<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the comm-link image show view with api data', function (): void {
    $commLink = CommLink::factory()->create([
        'cig_id' => 14001,
        'title' => 'Inside Star Citizen',
    ]);

    $image = Image::factory()->create([
        'alt' => 'Comm-Link Hero',
    ]);

    $commLink->images()->attach($image->id);

    $response = $this->get(route('web.comm-links.images.show', $image->id));

    $response->assertOk()
        ->assertViewIs('comm-links.images.show')
        ->assertSee('Comm-Link Image')
        ->assertSee('Comm-Link Hero')
        ->assertSee('Inside Star Citizen')
        ->assertSee((string) $commLink->cig_id);
});
