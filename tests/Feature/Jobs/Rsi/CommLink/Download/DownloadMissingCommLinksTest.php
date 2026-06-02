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

function hubApiResponse(string $data, int $status = 200, int $success = 1)
{
    return Http::response([
        'success' => $success,
        'code' => $success === 1 ? 'OK' : 'ERROR',
        'msg' => $success === 1 ? 'OK' : 'ERROR',
        'data' => $data,
    ], $status);
}

function hubApiLatestPageSequence(string $data)
{
    return Http::sequence()
        ->pushResponse(hubApiResponse($data))
        ->pushResponse(hubApiResponse(hubEndPageHtml()));
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

it('does not dispatch downloads for already-known first-page IDs', function () {
    Queue::fake();
    CommLink::factory()->create(['cig_id' => 21180]);
    CommLink::factory()->create(['cig_id' => 21184]);

    Http::fake([
        '*/api/hub/getCommlinkItems*' => hubApiLatestPageSequence(hubPageHtml([
            ['slug' => 'transmission', 'id' => 21184, 'title_slug' => 'Roadmap-Roundup', 'title' => 'Roadmap Roundup'],
            ['slug' => 'engineering', 'id' => 21180, 'title_slug' => 'ISC', 'title' => 'Inside Star Citizen'],
        ])),
    ]);

    (new DownloadMissingCommLinks)->handle();

    Queue::assertNotPushed(DownloadCommLink::class);
});

it('dispatches only new API IDs from the first page', function () {
    Queue::fake();
    CommLink::factory()->create(['cig_id' => 21180]);
    CommLink::factory()->create(['cig_id' => 21184]);

    Http::fake([
        '*/api/hub/getCommlinkItems*' => hubApiLatestPageSequence(hubPageHtml([
            ['slug' => 'transmission', 'id' => 21185, 'title_slug' => 'New', 'title' => 'New'],
            ['slug' => 'transmission', 'id' => 21184, 'title_slug' => 'Roadmap-Roundup', 'title' => 'Roadmap Roundup'],
            ['slug' => 'engineering', 'id' => 21180, 'title_slug' => 'ISC', 'title' => 'Inside Star Citizen'],
        ])),
    ]);

    (new DownloadMissingCommLinks)->handle();

    expect(dispatchedDownloadIds())->toBe([21185]);
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
        '*/api/hub/getCommlinkItems*' => hubApiLatestPageSequence($data),
    ]);

    (new DownloadMissingCommLinks)->handle();

    expect(dispatchedDownloadIds())->toBe([20952, 21119]);
});

it('does not paginate past the first hub API page', function () {
    Queue::fake();
    CommLink::factory()->create(['cig_id' => 21184]);

    Http::fake([
        '*/api/hub/getCommlinkItems*' => Http::sequence()
            ->pushResponse(hubApiResponse(hubPageHtml([
                ['slug' => 'transmission', 'id' => 21185, 'title_slug' => 'A', 'title' => 'A'],
            ])))
            ->pushResponse(hubApiResponse(hubPageHtml([
                ['slug' => 'engineering', 'id' => 21186, 'title_slug' => 'B', 'title' => 'B'],
            ])))
            ->pushResponse(hubApiResponse(hubEndPageHtml())),
    ]);

    (new DownloadMissingCommLinks)->handle();

    expect(dispatchedDownloadIds())->toBe([21185]);
    Http::assertSentCount(1);
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

it('does not dispatch downloads on unsuccessful hub API response', function () {
    Queue::fake();

    Http::fake([
        '*/api/hub/getCommlinkItems*' => hubApiResponse('', success: 0),
    ]);

    (new DownloadMissingCommLinks)->handle();

    Queue::assertNotPushed(DownloadCommLink::class);
});

it('filters out IDs below FIRST_COMM_LINK_ID', function () {
    Queue::fake();
    CommLink::factory()->create(['cig_id' => 12663]);

    $data = <<<'HTML'
    <a class="hub-block" href="/comm-link/transmission/100-Too-Low"></a>
    <a class="hub-block" href="/comm-link/transmission/12663-First"></a>
    HTML;

    Http::fake([
        '*/api/hub/getCommlinkItems*' => hubApiLatestPageSequence($data),
    ]);

    (new DownloadMissingCommLinks)->handle();

    Queue::assertNotPushed(DownloadCommLink::class);
});

it('dispatches gap-filling IDs between max db and max api', function () {
    Queue::fake();

    CommLink::factory()->create(['cig_id' => 21180]);

    $data = <<<'HTML'
    <a class="hub-block" href="/comm-link/transmission/21184-Roadmap"></a>
    HTML;

    Http::fake([
        '*/api/hub/getCommlinkItems*' => hubApiLatestPageSequence($data),
    ]);

    (new DownloadMissingCommLinks)->handle();

    expect(dispatchedDownloadIds())->toBe([21181, 21182, 21183, 21184]);
});

it('handles empty hub response gracefully', function () {
    Queue::fake();

    Http::fake([
        '*/api/hub/getCommlinkItems*' => hubApiResponse(hubEndPageHtml()),
    ]);

    (new DownloadMissingCommLinks)->handle();

    Queue::assertNotPushed(DownloadCommLink::class);
});
