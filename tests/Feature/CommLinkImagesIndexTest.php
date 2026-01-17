<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists comm-link images ordered by newest first', function (): void {
    $older = Image::factory()->create([
        'alt' => 'Older image',
    ]);
    $older->forceFill(['created_at' => now()->subDays(2)])->save();

    $newer = Image::factory()->create([
        'alt' => 'Newer image',
    ]);
    $newer->forceFill(['created_at' => now()->subDay()])->save();

    $commLink = CommLink::factory()->create([
        'title' => 'Inside Star Citizen',
    ]);
    $newer->commLinks()->attach($commLink->id);

    $response = $this->get(route('web.comm-links.images.index'));

    $response->assertOk()
        ->assertViewIs('comm-links.images.index')
        ->assertSeeInOrder([
            'Newer image',
            'Older image',
        ])
        ->assertSee('Inside Star Citizen');
});
