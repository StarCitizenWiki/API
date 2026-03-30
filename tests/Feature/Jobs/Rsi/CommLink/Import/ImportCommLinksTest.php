<?php

declare(strict_types=1);

use App\Jobs\Rsi\CommLink\Image\CreateImageMetadata;
use App\Jobs\Rsi\CommLink\Image\DispatchImageHashes;
use App\Jobs\Rsi\CommLink\Import\ImportCommLinks;
use App\Jobs\Rsi\CommLink\Translate\TranslateCommLinks;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

$storeCommLinkHtml = function (
    string $path,
    string $title = 'Test Comm-Link',
    string $publishedAt = 'January 1, 2020',
): void {
    $html = strtr(<<<'HTML'
<html>
<head>
    <title>:title - Roberts Space Industries | Follow the development of Star Citizen and Squadron 42</title>
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
        <div></div><div></div><div><p>:publishedAt</p></div>
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
HTML, [
        ':title' => $title,
        ':publishedAt' => $publishedAt,
    ]);

    Storage::disk('comm_links')->put($path, $html);
};

$importCommLinks = function (): CommLink {
    (new ImportCommLinks(-1))->handle();

    return CommLink::query()
        ->where('cig_id', 12663)
        ->firstOrFail();
};

it('imports the latest comm-link file and skips translation when auto translation is disabled', function () use ($storeCommLinkHtml, $importCommLinks): void {
    Storage::fake('comm_links');
    Queue::fake([CreateImageMetadata::class, DispatchImageHashes::class, TranslateCommLinks::class]);

    config()->set('services.comm_links.auto_translate_after_import', false);
    config()->set('services.deepl.auth_key', 'test-key');

    $storeCommLinkHtml('12663/2020-01-01_000000.html', 'Older Comm-Link');
    $storeCommLinkHtml('12663/2020-01-02_000000.html', 'Latest Comm-Link', 'January 2, 2020');

    $commLink = $importCommLinks();

    Queue::assertPushed(CreateImageMetadata::class, function (CreateImageMetadata $job): bool {
        return $job->commLinkIds === [12663];
    });
    Queue::assertPushed(DispatchImageHashes::class, function (DispatchImageHashes $job): bool {
        return $job->commLinkIds === [12663];
    });
    Queue::assertNotPushed(TranslateCommLinks::class);

    expect($commLink->title)->toBe('Latest Comm-Link')
        ->and($commLink->file)->toBe('2020-01-02_000000.html');
});

it('dispatches comm-link translation for imported ids when auto translation is enabled', function () use ($storeCommLinkHtml, $importCommLinks): void {
    Storage::fake('comm_links');
    Queue::fake([CreateImageMetadata::class, DispatchImageHashes::class, TranslateCommLinks::class]);

    config()->set('services.comm_links.auto_translate_after_import', true);
    config()->set('services.deepl.auth_key', 'test-key');

    $storeCommLinkHtml('12663/2020-01-01_000000.html');

    $commLink = $importCommLinks();

    Queue::assertPushed(TranslateCommLinks::class, function (TranslateCommLinks $job): bool {
        return $job->commLinkIds === [12663];
    });

    expect($commLink->title)->toBe('Test Comm-Link');
});
