<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows comm-link images filtered by media name', function (): void {
    $image = Image::factory()->create([
        'src' => '/i/carrack/carrack.webp',
    ]);

    $image->metadata()->create([
        'size' => 2048,
        'mime' => 'image/webp',
        'last_modified' => now(),
    ]);

    $response = $this->get(route('web.comm-links.images.index', [
        'search' => 'media-name',
        'query' => 'carrack',
    ]));

    $response->assertOk()
        ->assertSee('carrack.webp');
});
