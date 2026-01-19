<?php

declare(strict_types=1);

use App\Jobs\Rsi\CommLink\Download\DownloadCommLink;
use App\Services\RsiDownloadClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

it('downloads and stores a comm-link html file', function () {
    Storage::fake('comm_links');

    $html = "<html><body id=\"post\">'token' : 'abc123'</body></html>";

    Http::fake([
        '*' => Http::response($html, 200),
    ]);

    (new DownloadCommLink(12663, false))->handle(app(RsiDownloadClient::class));

    $files = Storage::disk('comm_links')->allFiles('12663');

    expect($files)->toHaveCount(1);

    $stored = Storage::disk('comm_links')->get($files[0]);

    expect($stored)->not->toContain('abc123');
});

it('keeps only the oldest and newest comm-link files per folder', function () {
    Storage::fake('comm_links');

    Storage::disk('comm_links')->put('12663/2020-01-01_000000.html', 'oldest');
    Storage::disk('comm_links')->put('12663/2020-01-02_000000.html', 'middle-1');
    Storage::disk('comm_links')->put('12663/2020-01-03_000000.html', 'middle-2');

    Carbon::setTestNow(Carbon::parse('2020-01-04 00:00:00'));

    Http::fake([
        '*' => Http::response('<html><body id="post">new</body></html>', 200),
    ]);

    (new DownloadCommLink(12663, false))->handle(app(RsiDownloadClient::class));

    Storage::disk('comm_links')->assertExists('12663/2020-01-01_000000.html');
    Storage::disk('comm_links')->assertExists('12663/2020-01-04_000000.html');
    Storage::disk('comm_links')->assertMissing('12663/2020-01-02_000000.html');
    Storage::disk('comm_links')->assertMissing('12663/2020-01-03_000000.html');

    Carbon::setTestNow();
});
