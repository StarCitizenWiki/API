<?php

declare(strict_types=1);

use App\Jobs\Rsi\CommLink\Image\CreateImageMetadata;
use App\Jobs\Rsi\CommLink\Image\DispatchImageHashes;
use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Jobs\Rsi\CommLink\Translate\TranslateCommLinks;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
    $files = Storage::disk('comm_links')->files('12663');
    sort($files);
    $latestFile = Str::afterLast(end($files), '/');

    (new ImportCommLink(12663, $latestFile))->handle();

    CreateImageMetadata::dispatch([12663]);
    DispatchImageHashes::dispatch([12663]);

    if ((bool) config('services.comm_links.auto_translate_after_import', false) && filled(config('services.deepl.auth_key'))) {
        TranslateCommLinks::dispatch([12663]);
    }

    return CommLink::query()
        ->where('cig_id', 12663)
        ->firstOrFail();
};

$storeAlexandriaShellHtml = function (
    string $path,
    string $title = 'Alexandria Shell',
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
        <div></div><div></div><div><p>January 1, 2020</p></div>
    </div>
</div>
<span class="comment-count">5</span>
<div id="layout-system"></div>
<script>
    window.__ALEXANDRIA__ = "https://robertsspaceindustries.com/alexandria/html/12663/en-us";
</script>
</body>
</html>
HTML, [':title' => $title]);

    Storage::disk('comm_links')->put($path, $html);
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

describe('dynamic content', function () use ($storeAlexandriaShellHtml): void {
    it('does not persist a comm link when the alexandria fetch fails', function () use ($storeAlexandriaShellHtml): void {
        Storage::fake('comm_links');

        $storeAlexandriaShellHtml('12663/2020-01-01_000000.html');

        Http::fake([
            'robertsspaceindustries.com/alexandria/*' => Http::response('', 500),
        ]);

        expect(fn () => (new ImportCommLink(12663, '2020-01-01_000000.html'))->handle())
            ->toThrow(RequestException::class);

        expect(CommLink::query()->where('cig_id', 12663)->exists())->toBeFalse();

        Http::assertSentCount(3);
    });

    it('fetches and splices alexandria content successfully', function () use ($storeAlexandriaShellHtml): void {
        Storage::fake('comm_links');

        $storeAlexandriaShellHtml('12663/2020-01-01_000000.html');

        Http::fake([
            'robertsspaceindustries.com/alexandria/*' => Http::response(
                '<g-banner-advanced :content=\'{"text":{"title":"Alexandria Title","paragraph":"Alexandria paragraph"}}\'></g-banner-advanced>'
            ),
        ]);

        (new ImportCommLink(12663, '2020-01-01_000000.html'))->handle();

        $commLink = CommLink::query()->where('cig_id', 12663)->firstOrFail();

        expect($commLink->title)->toBe('Alexandria Shell')
            ->and($commLink->getTranslation('translation', 'en'))->toContain('Alexandria Title')
            ->and(Storage::disk('comm_links')->get('12663/2020-01-01_000000.html'))->toContain('Alexandria Title');

        Http::assertSentCount(1);
    });

    it('retries failed alexandria fetches before failing the job', function (): void {
        $job = new ImportCommLink(12663, '2020-01-01_000000.html');

        expect($job->tries)->toBe(3)
            ->and($job->backoff)->toBe([60, 300]);
    });
});
