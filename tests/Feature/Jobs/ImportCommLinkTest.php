<?php

declare(strict_types=1);

use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Link;
use App\Models\System\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('imports comm-link content, images, and links', function () {
    Storage::fake('comm_links');

    $html = <<<'HTML'
<html>
<head>
    <title>Test Comm-Link - Roberts Space Industries | Follow the development of Star Citizen and Squadron 42</title>
    <meta property="og:url" content="https://robertsspaceindustries.com/comm-link/SCW/12663-API">
</head>
<body>
<div class="title-bar">
    <div class="title">
        <h1>Transmission</h1>
        <h2>General</h2>
    </div>
</div>
<div class="presented-by"></div><div></div><h1>Series Name</h1>
<div class="title-section">
    <div class="details">
        <div></div><div></div><div><p>January 1, 2020</p></div>
    </div>
</div>
<span class="comment-count">5</span>
<div id="post">
    <div class="segment"><p>Hello world</p></div>
    <img src="https://media.robertsspaceindustries.com/abcdef1234567890/source.jpg" alt="Test">
    <a href="https://example.com">Example</a>
</div>
</body>
</html>
HTML;

    Storage::disk('comm_links')->put('12663/2020-01-01_000000.html', $html);

    (new ImportCommLink(12663, '2020-01-01_000000.html'))->handle();

    $commLink = CommLink::query()->where('cig_id', 12663)->first();

    expect($commLink)->not->toBeNull()
        ->and($commLink->title)->toBe('Test Comm-Link')
        ->and($commLink->comment_count)->toBe(5)
        ->and($commLink->images_count)->toBeGreaterThanOrEqual(1)
        ->and($commLink->links_count)->toBe(1);

    $translation = $commLink?->getTranslation('translation', Language::ENGLISH, false);

    expect($translation)->not->toBeNull()
        ->and($translation)->toContain('Hello world')
        ->and(Image::query()->count())->toBeGreaterThanOrEqual(1)
        ->and($commLink->images_count)->toBeGreaterThanOrEqual(1)
        ->and(Link::query()->count())->toBe(1)
        ->and($commLink->links_count)->toBe(1);

});
