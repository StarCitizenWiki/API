<?php

declare(strict_types=1);

use App\Jobs\Rsi\CommLink\Download\DownloadCommLink;
use App\Jobs\Rsi\CommLink\Download\DownloadMissingCommLinks;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

function hubPageHtml(array $items): string
{
    $html = '';
    foreach ($items as $item) {
        $html .= <<<HTML
        <a class="content-block2 hub-block full"
          href="/comm-link/{$item['slug']}/{$item['id']}-{$item['title_slug']}"
        >
          <div class="title">{$item['title']}</div>
        </a>
        HTML;
    }

    return $html;
}

function hubEndPageHtml(): string
{
    return '<div class="no-results">There is nothing.</div><div class="cboth"></div>';
}

function hubApiResponse(string $data, int $status = 200)
{
    return Http::response([
        'success' => 1,
        'code' => 'OK',
        'msg' => 'OK',
        'data' => $data,
    ], $status);
}

function dispatchedDownloadIds(): array
{
    return collect(Queue::pushedJobs()[DownloadCommLink::class] ?? [])
        ->map(fn (array $entry) => $entry['job']->commLinkId)
        ->unique()
        ->sort()
        ->values()
        ->all();
}

it('extracts IDs from a single hub API page', function () {
    Queue::fake();
    // Create a DB record at max ID so gap-fill doesn't flood the results
    CommLink::factory()->create(['cig_id' => 21184]);

    Http::fake([
        '*/api/hub/getCommlinkItems*' => Http::sequence()
            ->pushResponse(hubApiResponse(hubPageHtml([
                ['slug' => 'transmission', 'id' => 21184, 'title_slug' => 'Roadmap-Roundup', 'title' => 'Roadmap Roundup'],
                ['slug' => 'engineering', 'id' => 21180, 'title_slug' => 'ISC', 'title' => 'Inside Star Citizen'],
            ])))
            ->pushResponse(hubApiResponse(hubEndPageHtml())),
    ]);

    (new DownloadMissingCommLinks)->handle();

    $ids = dispatchedDownloadIds();
    // API IDs only (no gap-fill since DB already at max)
    expect($ids)->toBe([21180, 21184]);
});

it('handles mixed href formats with and without channel slug', function () {
    Queue::fake();
    CommLink::factory()->create(['cig_id' => 21184]);

    $data = <<<'HTML'
    <a class="hub-block" href="/comm-link/transmission/21184-Roadmap-Roundup"></a>
    <a class="hub-block" href="/comm-link//21119-DefenseCon"></a>
    <a class="hub-block" href="/comm-link/spectrum-dispatch/20952-Roadmap"></a>
    HTML;

    Http::fake([
        '*/api/hub/getCommlinkItems*' => Http::sequence()
            ->pushResponse(hubApiResponse($data))
            ->pushResponse(hubApiResponse(hubEndPageHtml())),
    ]);

    (new DownloadMissingCommLinks)->handle();

    $ids = dispatchedDownloadIds();
    expect($ids)->toContain(20952, 21119, 21184);
});

it('paginates through multiple pages and stops at sentinel', function () {
    Queue::fake();
    CommLink::factory()->create(['cig_id' => 21184]);

    Http::fake([
        '*/api/hub/getCommlinkItems*' => Http::sequence()
            ->pushResponse(hubApiResponse(hubPageHtml([
                ['slug' => 'transmission', 'id' => 21184, 'title_slug' => 'A', 'title' => 'A'],
            ])))
            ->pushResponse(hubApiResponse(hubPageHtml([
                ['slug' => 'engineering', 'id' => 21180, 'title_slug' => 'B', 'title' => 'B'],
            ])))
            ->pushResponse(hubApiResponse(hubEndPageHtml())),
    ]);

    (new DownloadMissingCommLinks)->handle();

    $ids = dispatchedDownloadIds();
    expect($ids)->toBe([21180, 21184]);
});

it('does not dispatch downloads on server error', function () {
    Queue::fake();

    Http::fake([
        '*/api/hub/getCommlinkItems*' => Http::response('', 500),
    ]);

    (new DownloadMissingCommLinks)->handle();

    Queue::assertNotPushed(DownloadCommLink::class);
});

it('returns without retry on client error', function () {
    Queue::fake();

    Http::fake([
        '*/api/hub/getCommlinkItems*' => Http::response('', 403),
    ]);

    (new DownloadMissingCommLinks)->handle();

    Queue::assertNotPushed(DownloadCommLink::class);
});

it('filters out IDs below FIRST_COMM_LINK_ID', function () {
    Queue::fake();
    CommLink::factory()->create(['cig_id' => 21184]);

    $data = <<<'HTML'
    <a class="hub-block" href="/comm-link/transmission/100-Too-Low"></a>
    <a class="hub-block" href="/comm-link/transmission/21184-Valid"></a>
    <a class="hub-block" href="/comm-link/transmission/12663-First"></a>
    HTML;

    Http::fake([
        '*/api/hub/getCommlinkItems*' => Http::sequence()
            ->pushResponse(hubApiResponse($data))
            ->pushResponse(hubApiResponse(hubEndPageHtml())),
    ]);

    (new DownloadMissingCommLinks)->handle();

    $ids = dispatchedDownloadIds();
    // Gap-fill: 21184 already in DB, so no gap-fill IDs. API IDs: 12663, 21184
    expect($ids)->toBe([12663, 21184]);
});

it('dispatches gap-filling IDs between max db and max api', function () {
    Queue::fake();

    // Simulate DB has up to cig_id 21180
    CommLink::factory()->create(['cig_id' => 21180]);

    $data = <<<'HTML'
    <a class="hub-block" href="/comm-link/transmission/21184-Roadmap"></a>
    HTML;

    Http::fake([
        '*/api/hub/getCommlinkItems*' => Http::sequence()
            ->pushResponse(hubApiResponse($data))
            ->pushResponse(hubApiResponse(hubEndPageHtml())),
    ]);

    (new DownloadMissingCommLinks)->handle();

    $ids = dispatchedDownloadIds();
    // Should dispatch: 21184 (from API) + 21181, 21182, 21183, 21184 (gap-fill from 21181 to 21184)
    // After unique: 21181, 21182, 21183, 21184
    expect($ids)->toBe([21181, 21182, 21183, 21184]);
});

it('handles empty hub response gracefully', function () {
    Queue::fake();

    Http::fake([
        '*/api/hub/getCommlinkItems*' => Http::sequence()
            ->pushResponse(hubApiResponse(hubEndPageHtml())),
    ]);

    (new DownloadMissingCommLinks)->handle();

    Queue::assertNotPushed(DownloadCommLink::class);
});
