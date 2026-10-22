<?php

declare(strict_types=1);

use App\Jobs\Game\SyncPaintImages;
use App\Models\Game\Item;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

function paintWikiFixture(): string
{
    return file_get_contents(__DIR__.'/../../../Fixtures/Wiki/paints.wikitext') ?: '';
}

/**
 * Fakes the wiki api keyed on the request query shape: list=search requests
 * get the configured subpage titles, prop=revisions requests get the fixture
 * page, prop=imageinfo requests get the configured file pages.
 */
function fakePaintWiki(array $filePages, array $searchTitles = [
    '300 series/Paints',
    '600 series/Paints',
    '85X series/Paints',
    'Ares series/Paints',
    'Eclipse series/Paints',
    'Gladius series/Paints',
    'Hammerhead series/Paints',
    'Hurricane series/Paints',
    'Vanguard series/Paints',
    'Valkyrie series/Paints',
], ?string $content = null): void
{
    config(['images.throttle_microseconds' => 0]);

    Http::fake(function (Request $request) use ($filePages, $searchTitles, $content) {
        parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

        if (($query['list'] ?? null) === 'search') {
            return Http::response([
                'query' => [
                    'search' => array_map(
                        fn (string $title): array => ['title' => $title],
                        $searchTitles,
                    ),
                ],
            ]);
        }

        if (($query['prop'] ?? null) === 'revisions') {
            return Http::response([
                'query' => [
                    'pages' => [
                        [
                            'pageid' => 1,
                            'ns' => 0,
                            'title' => 'Gladius series/Paints',
                            'revisions' => [
                                ['slots' => ['main' => ['content' => $content ?? paintWikiFixture()]]],
                            ],
                        ],
                    ],
                ],
            ]);
        }

        return Http::response(['query' => ['pages' => $filePages]]);
    });
}

function paintFilePage(string $title, bool $missing = false): array
{
    $page = ['ns' => 6, 'title' => "File:{$title}"];

    if ($missing) {
        return [...$page, 'missing' => true];
    }

    $slug = str_replace(' ', '_', $title);

    return [...$page, 'imageinfo' => [[
        'thumburl' => "https://media.starcitizen.tools/thumb/{$slug}/600px-{$slug}?c4ch3",
        'thumbwidth' => 600,
        'thumbheight' => 338,
        'url' => "https://media.starcitizen.tools/{$slug}?c4ch3",
        'width' => 1920,
        'height' => 1080,
    ]]];
}

function paintFilePages(): array
{
    return [
        paintFilePage('Gladius_in_space_-_Isometric.jpg'),
        paintFilePage('Gladius Deck The Hull in space - Isometric.jpg'),
        paintFilePage('Gladius Deck The Hull in space - Above.jpg'),
        paintFilePage('Gladius Biscuit Bay in space - Isometric.jpg', missing: true),
        paintFilePage('Gladius Biscuit Bay in space - Above.jpg'),
        paintFilePage('Gladius Renegade - Paint scheme.png'),
        paintFilePage('Prowler Stormbringer in space - Isometric.jpg'),
        paintFilePage('Gladius Discontinued in space - Isometric.jpg'),
        paintFilePage('Gladius Invictus Blue - Paint scheme.png'),
    ];
}

it('writes one ranked entry per resolved file, preferring the isometric file', function (): void {
    $deckTheHull = Item::factory()->create(['uuid' => '9242a0d1-0519-450d-aad2-b6230841ab15']);
    Item::factory()->create(['uuid' => '0aa68029-e21d-4b48-a31d-b7baaa57f31c']);
    Item::factory()->create(['uuid' => '3d9c1a24-7e58-4c62-b1f0-9a8d6c3e5b21']);
    Item::factory()->create(['uuid' => '5b8e2f7c-1a4d-4e93-8c2f-6d7a0b9e4c31']);
    Item::factory()->create(['uuid' => '7f4a9d2b-8c63-4e17-a5d9-0b2e7c6f8a43']);

    fakePaintWiki(paintFilePages());

    (new SyncPaintImages)->handle();

    $deckTheHull->refresh();

    expect($deckTheHull->images)->toHaveCount(2)
        ->and($deckTheHull->images[0])->toMatchArray([
            'source' => 'starcitizen.tools/paints',
            'thumbnail_url' => 'https://media.starcitizen.tools/thumb/Gladius_Deck_The_Hull_in_space_-_Isometric.jpg/600px-Gladius_Deck_The_Hull_in_space_-_Isometric.jpg',
            'thumbnail_width' => 600,
            'thumbnail_height' => 338,
            'original_url' => 'https://media.starcitizen.tools/Gladius_Deck_The_Hull_in_space_-_Isometric.jpg',
            'original_width' => 1920,
            'original_height' => 1080,
        ])
        ->and($deckTheHull->images[1]['original_url'])->toBe('https://media.starcitizen.tools/Gladius_Deck_The_Hull_in_space_-_Above.jpg');

    // One search, one revisions batch, one imageinfo batch for the unique files.
    Http::assertSentCount(3);
});

it('falls back to the next preference when the chosen file resolution is dropped', function (): void {
    $biscuitBay = Item::factory()->create(['uuid' => '3d9c1a24-7e58-4c62-b1f0-9a8d6c3e5b21']);

    fakePaintWiki(paintFilePages());

    (new SyncPaintImages)->handle();

    $biscuitBay->refresh();

    expect($biscuitBay->images)->toHaveCount(1)
        ->and($biscuitBay->images[0]['original_url'])->toBe('https://media.starcitizen.tools/Gladius_Biscuit_Bay_in_space_-_Above.jpg');
});

it('prefers the isometric column over the paint scheme swatch regardless of file names', function (): void {
    $imperialRed = Item::factory()->create(['uuid' => '99cbe3b1-e153-4ebb-bb24-5cc48af5f21d']);

    $content = <<<'WIKI'
        ==="Imperial Red"===
        {| class="wikitable"
        !Paint scheme
        !Isometric
        !Above
        |-
        |[[File:300i Imperial Red - Paint scheme.png|center|frameless|128x72px]]
        |[[File:Imperial Red 300i.png|center|frameless|99x99px]]
        |[[File:placeholderv2.png|center|frameless|128x72px|link=|class=noviewer]]
        |}
        {| class="wikitable"
        !Description
        !Price ($)
        |-
        |Red in colour with white accents.
        '''Limited edition. Only available during an event.<ref name="Event">{{Cite RSI|url=https://example.com|text=Event|accessdate=2026-01-01}}</ref>
        | (tbc)
        |}
        {| class="wikitable"
        !UUID
        !Name
        |-
        |99cbe3b1-e153-4ebb-bb24-5cc48af5f21d
        |Paint_300i_Red_White
        |}
        WIKI;

    fakePaintWiki([
        paintFilePage('300i Imperial Red - Paint scheme.png'),
        paintFilePage('Imperial Red 300i.png'),
    ], content: $content);

    (new SyncPaintImages)->handle();

    $imperialRed->refresh();

    expect($imperialRed->images)->toHaveCount(2)
        ->and($imperialRed->images[0]['original_url'])->toBe('https://media.starcitizen.tools/Imperial_Red_300i.png')
        ->and($imperialRed->images[1]['original_url'])->toBe('https://media.starcitizen.tools/300i_Imperial_Red_-_Paint_scheme.png');
});

it('falls back to file names when a section has no table headers', function (): void {
    $renegade = Item::factory()->create(['uuid' => '5b8e2f7c-1a4d-4e93-8c2f-6d7a0b9e4c31']);

    $content = <<<'WIKI'
        ==="Renegade"===
        {| class="wikitable"
        |-
        |[[File:Gladius Renegade - Paint scheme.png|center|frameless|128x72px]]
        |[[File:Gladius Renegade in space - Isometric.jpg|center|frameless|128x72px]]
        |}
        {| class="wikitable"
        !UUID
        !Name
        |-
        |5b8e2f7c-1a4d-4e93-8c2f-6d7a0b9e4c31
        |Paint_Gladius_Renegade
        |}
        WIKI;

    fakePaintWiki([
        paintFilePage('Gladius Renegade - Paint scheme.png'),
        paintFilePage('Gladius Renegade in space - Isometric.jpg'),
    ], content: $content);

    (new SyncPaintImages)->handle();

    $renegade->refresh();

    expect($renegade->images)->toHaveCount(2)
        ->and($renegade->images[0]['original_url'])->toBe('https://media.starcitizen.tools/Gladius_Renegade_in_space_-_Isometric.jpg')
        ->and($renegade->images[1]['original_url'])->toBe('https://media.starcitizen.tools/Gladius_Renegade_-_Paint_scheme.png');
});

it('resolves underscore-form file links through the space-form api title', function (): void {
    $stormbringer = Item::factory()->create(['uuid' => '28450c13-1111-4222-8333-444455556666']);

    fakePaintWiki(paintFilePages());

    (new SyncPaintImages)->handle();

    $stormbringer->refresh();

    expect($stormbringer->images)->toHaveCount(1)
        ->and($stormbringer->images[0]['original_url'])->toBe('https://media.starcitizen.tools/Prowler_Stormbringer_in_space_-_Isometric.jpg');
});

it('gives both uuids of a two-uuid section the section image', function (): void {
    $renegade = Item::factory()->create(['uuid' => '5b8e2f7c-1a4d-4e93-8c2f-6d7a0b9e4c31']);
    $renegadeWarbond = Item::factory()->create(['uuid' => '7f4a9d2b-8c63-4e17-a5d9-0b2e7c6f8a43']);

    fakePaintWiki(paintFilePages());

    (new SyncPaintImages)->handle();

    $renegade->refresh();
    $renegadeWarbond->refresh();

    expect($renegade->images)->toHaveCount(1)
        ->and($renegadeWarbond->images)->toHaveCount(1)
        ->and($renegade->images[0]['original_url'])->toBe('https://media.starcitizen.tools/Gladius_Renegade_-_Paint_scheme.png')
        ->and($renegadeWarbond->images[0])->toBe($renegade->images[0]);
});

it('writes nothing for placeholder-only and uuid-less sections', function (): void {
    $deckTheHull = Item::factory()->create(['uuid' => '9242a0d1-0519-450d-aad2-b6230841ab15']);
    $unreleased = Item::factory()->create(['uuid' => '0aa68029-e21d-4b48-a31d-b7baaa57f31c']);
    Item::factory()->create(['uuid' => '3d9c1a24-7e58-4c62-b1f0-9a8d6c3e5b21']);
    Item::factory()->create(['uuid' => '5b8e2f7c-1a4d-4e93-8c2f-6d7a0b9e4c31']);
    Item::factory()->create(['uuid' => '7f4a9d2b-8c63-4e17-a5d9-0b2e7c6f8a43']);

    fakePaintWiki(paintFilePages());

    (new SyncPaintImages)->handle();

    $deckTheHull->refresh();
    $unreleased->refresh();

    expect($unreleased->images)->toBeNull()
        ->and($deckTheHull->images[0]['original_url'])->not->toBe('https://media.starcitizen.tools/Gladius_in_space_-_Isometric.jpg')
        ->and(Item::query()->whereNotNull('images')->count())->toBe(4);
});

it('counts uuids without a game item as unmatched and writes nothing', function (): void {
    Item::factory()->create(['uuid' => '9242a0d1-0519-450d-aad2-b6230841ab15']);
    Item::factory()->create(['uuid' => '0aa68029-e21d-4b48-a31d-b7baaa57f31c']);
    Item::factory()->create(['uuid' => '3d9c1a24-7e58-4c62-b1f0-9a8d6c3e5b21']);
    Item::factory()->create(['uuid' => '5b8e2f7c-1a4d-4e93-8c2f-6d7a0b9e4c31']);
    Item::factory()->create(['uuid' => '7f4a9d2b-8c63-4e17-a5d9-0b2e7c6f8a43']);
    Item::factory()->create(['uuid' => '28450c13-1111-4222-8333-444455556666']);

    fakePaintWiki(paintFilePages());

    Log::spy();

    (new SyncPaintImages)->handle();

    expect(Item::query()->whereNotNull('images')->count())->toBe(5);

    Log::shouldHaveReceived('info')->once()->withArgs(
        fn ($message, $context) => $context['uuids_unmatched'] === 1 && $context['items_updated'] === 5
    );
});

it('logs a run summary with all counters', function (): void {
    Item::factory()->create(['uuid' => '9242a0d1-0519-450d-aad2-b6230841ab15']);

    fakePaintWiki(paintFilePages());

    Log::spy();
    $summary = null;

    (new SyncPaintImages)->handle();

    Log::shouldHaveReceived('info')->once()->withArgs(
        function ($message, $context) use (&$summary): bool {
            $summary = $context;

            return $message === 'Paint images sync completed';
        }
    );

    expect($summary)->toMatchArray([
        'pages' => 1,
        'sections' => 6,
        'uuids_found' => 7,
        'uuids_unmatched' => 6,
        'files_missing' => 1,
        'items_updated' => 1,
        'errors' => 0,
    ])->and($summary)->toHaveKey('duration');
});
