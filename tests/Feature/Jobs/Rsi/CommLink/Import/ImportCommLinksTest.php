<?php

declare(strict_types=1);

use App\Jobs\Rsi\CommLink\Image\CreateImageMetadata;
use App\Jobs\Rsi\CommLink\Image\DispatchImageHashes;
use App\Jobs\Rsi\CommLink\Import\ImportCommLinks;
use App\Jobs\Rsi\CommLink\Translate\TranslateCommLinks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('does not dispatch comm-link translation after import when auto translation is disabled', function (): void {
    Storage::fake('comm_links');
    Queue::fake([CreateImageMetadata::class, DispatchImageHashes::class, TranslateCommLinks::class]);

    config()->set('services.comm_links.auto_translate_after_import', false);
    config()->set('services.deepl.auth_key', 'test-key');

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

    (new ImportCommLinks(-1))->handle();

    Queue::assertPushed(CreateImageMetadata::class);
    Queue::assertPushed(DispatchImageHashes::class);
    Queue::assertNotPushed(TranslateCommLinks::class);
});

it('dispatches comm-link translation for imported ids when auto translation is enabled', function (): void {
    Storage::fake('comm_links');
    Queue::fake([CreateImageMetadata::class, DispatchImageHashes::class, TranslateCommLinks::class]);

    config()->set('services.comm_links.auto_translate_after_import', true);
    config()->set('services.deepl.auth_key', 'test-key');

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

    (new ImportCommLinks(-1))->handle();

    Queue::assertPushed(TranslateCommLinks::class, function (TranslateCommLinks $job): bool {
        return $job->commLinkIds === [12663];
    });
});
