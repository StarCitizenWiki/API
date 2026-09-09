<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Vehicle\SyncVehicleCuratedData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleCuratedData;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

function curatedWikiFixture(string $name): string
{
    return file_get_contents(__DIR__.'/../../../../Fixtures/Wiki/'.$name) ?: '';
}

/**
 * Fakes the wiki api keyed on the request query shape: generator=categorymembers
 * requests get the configured category titles, prop=revisions requests get the
 * configured pages/redirects.
 */
function fakeWiki(array $shipTitles, array $groundTitles, array $pages, array $redirects = []): void
{
    config(['images.throttle_microseconds' => 0]);

    Http::fake(function (Request $request) use ($shipTitles, $groundTitles, $pages, $redirects) {
        parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

        if (($query['generator'] ?? null) === 'categorymembers') {
            $titles = $query['gcmtitle'] === 'Category:Ships' ? $shipTitles : $groundTitles;

            return Http::response([
                'query' => [
                    'pages' => array_map(
                        fn (int $index, string $title): array => ['pageid' => $index, 'ns' => 0, 'title' => $title],
                        array_keys($titles),
                        $titles,
                    ),
                ],
            ]);
        }

        return Http::response([
            'query' => [
                'redirects' => $redirects,
                'pages' => $pages,
            ],
        ]);
    });
}

function wikiPage(string $title, int $revid, string $content): array
{
    return [
        'pageid' => 42,
        'ns' => 0,
        'title' => $title,
        'revisions' => [
            [
                'revid' => $revid,
                'slots' => [
                    'main' => [
                        'content' => $content,
                    ],
                ],
            ],
        ],
    ];
}

it('upserts sanitized curated data for a known vehicle', function (): void {
    $vehicle = Vehicle::factory()->create(['uuid' => 'ce937681-caa2-4cfa-ab80-f5bf2a5b9a6c']);

    fakeWiki(
        shipTitles: ['300i'],
        groundTitles: [],
        pages: [wikiPage('300i', 12345, curatedWikiFixture('300i.wikitext'))],
    );

    (new SyncVehicleCuratedData)->handle();

    $row = VehicleCuratedData::query()->where('game_vehicle_id', $vehicle->id)->first();

    expect($row)->not->toBeNull()
        ->and($row->game_vehicle_id)->toBe($vehicle->id)
        ->and($row->original_pledge_price)->toBe(55)
        ->and($row->concept_date->toDateString())->toBe('2013-06-21')
        ->and($row->sale_date->toDateString())->toBe('2013-06-21')
        ->and($row->wiki_page_title)->toBe('300i')
        ->and($row->wiki_revision_id)->toBe(12345)
        ->and($row->trailer_url)->toBe('https://www.youtube.com/watch?v=qDQ8dbE8qSo')
        ->and($row->synced_at)->not->toBeNull()
        ->and($row->raw)->toMatchArray([
            'uuid' => 'ce937681-caa2-4cfa-ab80-f5bf2a5b9a6c',
            'name' => '300i',
        ]);
});

it('sanitizes year-only dates, lore years, nested templates, multi qa urls and wikilinks', function (): void {
    $vehicle = Vehicle::factory()->create(['uuid' => '11111111-2222-3333-4444-555555555555']);

    $wikitext = <<<'WIKITEXT'
{{Vehicle
| uuid                            = 11111111-2222-3333-4444-555555555555
| name                            = Edge Case
| originalpledgecost              = 250
| conceptdate                     = {{SDA|2665}}
| saledate                        = 2017
| retiredate                      = 2944
| pledgeavailability              = Limited edition[[C8 Pisces#Acquisition|*]]
| qaurl                           = https://example.com/qa-one ; https://example.com/qa-two ;
| presentationurl                 = https://example.com/pres-one ; https://example.com/pres-two
| trailerurl                      = https://www.youtube.com/watch?v=example
}}
WIKITEXT;

    fakeWiki(shipTitles: ['Edge Case'], groundTitles: [], pages: [wikiPage('Edge Case', 99, $wikitext)]);

    (new SyncVehicleCuratedData)->handle();

    $row = VehicleCuratedData::query()->where('game_vehicle_id', $vehicle->id)->first();

    expect($row)->not->toBeNull()
        ->and($row->original_pledge_price)->toBe(250)
        ->and($row->concept_date)->toBeNull()
        ->and($row->sale_date)->toBeNull()
        ->and($row->retire_date)->toBeNull()
        ->and($row->pledge_availability)->toBe('Limited edition')
        ->and($row->qa_urls)->toBe(['https://example.com/qa-one', 'https://example.com/qa-two'])
        ->and($row->presentation_urls)->toBe(['https://example.com/pres-one', 'https://example.com/pres-two'])
        ->and($row->raw['conceptdate'])->toBe('{{SDA|2665}}');
});

it('skips a page whose wiki revision is already stored', function (): void {
    $vehicle = Vehicle::factory()->create(['uuid' => 'ce937681-caa2-4cfa-ab80-f5bf2a5b9a6c']);
    $syncedAt = now()->subDays(7)->startOfSecond();

    VehicleCuratedData::query()->create([
        'game_vehicle_id' => $vehicle->id,
        'wiki_page_title' => '300i',
        'wiki_revision_id' => 12345,
        'synced_at' => $syncedAt,
    ]);

    Log::spy();
    fakeWiki(
        shipTitles: ['300i'],
        groundTitles: [],
        pages: [wikiPage('300i', 12345, curatedWikiFixture('300i.wikitext'))],
    );

    (new SyncVehicleCuratedData)->handle();

    expect(VehicleCuratedData::query()->where('game_vehicle_id', $vehicle->id)->first()->synced_at->equalTo($syncedAt))->toBeTrue();

    Log::shouldHaveReceived('info')->once()->withArgs(
        fn ($message, $context) => $context['unchanged_skipped'] === 1 && $context['upserted'] === 0
    );
});

it('counts redirected titles and processes the target once', function (): void {
    Vehicle::factory()->create(['uuid' => 'ce937681-caa2-4cfa-ab80-f5bf2a5b9a6c']);

    Log::spy();
    fakeWiki(
        shipTitles: ['300i', '300i Explorer'],
        groundTitles: [],
        pages: [wikiPage('300i', 12345, curatedWikiFixture('300i.wikitext'))],
        redirects: [['from' => '300i Explorer', 'to' => '300i']],
    );

    (new SyncVehicleCuratedData)->handle();

    expect(VehicleCuratedData::query()->count())->toBe(1);

    Log::shouldHaveReceived('info')->once()->withArgs(
        fn ($message, $context) => $context['redirects_skipped'] === 1 && $context['upserted'] === 1
    );
});

it('counts pages without the vehicle template or uuid param and stores nothing', function (): void {
    $nautilus = "{{Vehicle\n| name = Nautilus\n| productionstate = Announced\n}}";
    $plain = 'Just some wikitext without a vehicle infobox.';

    Log::spy();
    fakeWiki(
        shipTitles: ['Nautilus', 'Some Page'],
        groundTitles: [],
        pages: [
            wikiPage('Nautilus', 1, $nautilus),
            wikiPage('Some Page', 2, $plain),
        ],
    );

    (new SyncVehicleCuratedData)->handle();

    expect(VehicleCuratedData::query()->count())->toBe(0);

    Log::shouldHaveReceived('debug')->once()->withArgs(
        fn ($message, $context) => $context['title'] === 'Nautilus'
    );
    Log::shouldHaveReceived('info')->once()->withArgs(
        fn ($message, $context) => $context['no_template_skipped'] === 1 && $context['no_uuid_skipped'] === 1
    );
});

it('warns and skips when the uuid has no game vehicle', function (): void {
    $wikitext = "{{Vehicle\n| uuid = 00000000-0000-0000-0000-000000000001\n| name = Gladius Dunlevy\n}}";

    Log::spy();
    fakeWiki(
        shipTitles: ['Gladius Dunlevy'],
        groundTitles: [],
        pages: [wikiPage('Gladius Dunlevy', 7, $wikitext)],
    );

    (new SyncVehicleCuratedData)->handle();

    expect(VehicleCuratedData::query()->count())->toBe(0);

    Log::shouldHaveReceived('warning')->once()->withArgs(
        fn ($message, $context) => $context['title'] === 'Gladius Dunlevy'
            && $context['uuid'] === '00000000-0000-0000-0000-000000000001'
    );
    Log::shouldHaveReceived('info')->once()->withArgs(
        fn ($message, $context) => $context['uuid_unmatched'] === 1
    );
});

it('logs a run summary with all counters', function (): void {
    Vehicle::factory()->create(['uuid' => 'ce937681-caa2-4cfa-ab80-f5bf2a5b9a6c']);

    fakeWiki(
        shipTitles: ['300i'],
        groundTitles: ['Cyclone'],
        pages: [wikiPage('300i', 5, curatedWikiFixture('300i.wikitext'))],
    );

    Log::spy();
    $summary = null;

    (new SyncVehicleCuratedData)->handle();

    Log::shouldHaveReceived('info')->once()->withArgs(
        function ($message, $context) use (&$summary): bool {
            $summary = $context;

            return $message === 'Vehicle curated data sync completed';
        }
    );

    expect($summary)->toMatchArray([
        'scanned' => 2,
        'pages_with_content' => 1,
        'no_template_skipped' => 0,
        'unchanged_skipped' => 0,
        'redirects_skipped' => 0,
        'no_uuid_skipped' => 0,
        'uuid_unmatched' => 0,
        'upserted' => 1,
    ])->and($summary)->toHaveKey('duration');
});
