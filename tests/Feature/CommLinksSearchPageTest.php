<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Testing\TestResponse;
use Symfony\Component\DomCrawler\Crawler;

function commLinksSearchCrawler(TestResponse $response): Crawler
{
    return new Crawler($response->getContent());
}

function assertSearchFormContract(
    TestResponse $response,
    string $action,
    string $method,
    array $fields,
    array $fieldValues = [],
    ?string $enctype = null,
): void {
    $matchingForms = [];

    foreach (commLinksSearchCrawler($response)->filter('form') as $formElement) {
        $form = new Crawler($formElement);

        if ($form->attr('action') !== $action || strtoupper((string) $form->attr('method')) !== strtoupper($method)) {
            continue;
        }

        if ($enctype !== null && $form->attr('enctype') !== $enctype) {
            continue;
        }

        $matches = true;

        foreach ($fields as $field) {
            if ($form->filter(sprintf('[name="%s"]', $field))->count() === 0) {
                $matches = false;

                break;
            }
        }

        if (! $matches) {
            continue;
        }

        foreach ($fieldValues as $field => $value) {
            $input = $form->filter(sprintf('[name="%s"]', $field));

            if ($input->count() === 0 || $input->attr('value') !== $value) {
                $matches = false;

                break;
            }
        }

        if ($matches) {
            $matchingForms[] = $form;
        }
    }

    expect(count($matchingForms))->toBe(1);
}

function assertCommLinkPageLinkPresent(TestResponse $response, string $href, ?string $text = null): void
{
    $links = commLinksSearchCrawler($response)->filter(sprintf('a[href="%s"]', $href));

    expect($links->count())->toBeGreaterThan(0);

    if ($text !== null) {
        expect($links->first()->text())->toContain($text);
    }
}

it('renders the comm-link search page with working search forms', function (): void {
    $response = $this->get(route('web.comm-links.search'));

    $response->assertSuccessful()
        ->assertSeeText('Comm-Link Search')
        ->assertSeeText('Text Search')
        ->assertSeeText('Image Search')
        ->assertSeeText('Search title')
        ->assertSeeText('Search content')
        ->assertSeeText('Search media URL')
        ->assertSeeText('Search media name')
        ->assertSeeText('Search by image');

    assertSearchFormContract(
        $response,
        route('web.comm-links.index'),
        'GET',
        ['search', 'query'],
        ['search' => 'title'],
    );
    assertSearchFormContract(
        $response,
        route('web.comm-links.index'),
        'GET',
        ['search', 'query'],
        ['search' => 'content'],
    );
    assertSearchFormContract(
        $response,
        route('web.comm-links.index'),
        'GET',
        ['search', 'url'],
        ['search' => 'media-url'],
    );
    assertSearchFormContract(
        $response,
        route('web.comm-links.images.search'),
        'GET',
        ['query'],
    );
    assertSearchFormContract(
        $response,
        route('web.comm-links.images.reverse-search'),
        'POST',
        ['image', 'similarity'],
        enctype: 'multipart/form-data',
    );
});

it('rewrites numeric title searches to id filters and seeds the tabulator payload', function (): void {
    CommLink::factory()->create([
        'cig_id' => 14001,
        'title' => 'Roadmap Roundup',
    ]);
    CommLink::factory()->create([
        'cig_id' => 14002,
        'title' => 'Monthly Report',
    ]);

    $response = $this->get(route('web.comm-links.index', [
        'search' => 'title',
        'query' => '14001',
    ]));

    $response->assertOk()
        ->assertViewHas('searchType', 'title')
        ->assertViewHas('searchQuery', '14001')
        ->assertViewHas('initialFilters', [
            ['field' => 'id', 'value' => '14001'],
        ])
        ->assertSeeText('Comm-Links');
});

it('filters comm-links by content in the tabulator payload', function (): void {
    $matching = CommLink::factory()->create([
        'cig_id' => 15001,
        'title' => 'Quantum Systems Update',
    ]);
    $matching->setTranslation('translation', 'en', 'The latest quantum jump drive calibration guide.');
    $matching->save();

    $nonMatching = CommLink::factory()->create([
        'cig_id' => 15002,
        'title' => 'Cargo and Trade Update',
    ]);
    $nonMatching->setTranslation('translation', 'en', 'Cargo manifests and trade lane updates.');
    $nonMatching->save();

    $response = $this->get(route('web.comm-links.index', [
        'search' => 'content',
        'query' => 'quantum jump drive',
    ]));

    $response->assertOk()
        ->assertViewHas('searchType', 'content')
        ->assertViewHas('searchQuery', 'quantum jump drive')
        ->assertViewHas('initialFilters', [])
        ->assertSeeText('Comm-Links');
});

it('finds comm-links by media URL and renders result links', function (): void {
    $image = Image::factory()->create([
        'src' => '/i/matched-dir/alpha-banner.webp',
        'dir' => 'i',
    ]);

    $older = CommLink::factory()->create([
        'cig_id' => 15001,
        'title' => 'Alpha Update',
    ]);
    $newer = CommLink::factory()->create([
        'cig_id' => 15002,
        'title' => 'Beta Update',
    ]);

    $image->commLinks()->attach([$older->id, $newer->id]);

    $response = $this->get(route('web.comm-links.index', [
        'search' => 'media-url',
        'url' => 'https://media.robertsspaceindustries.com/i/matched-dir/alpha-banner.webp',
    ]));

    $response->assertOk()
        ->assertSeeText('Comm-Links')
        ->assertSeeText('Media URL Results')
        ->assertSeeText('https://media.robertsspaceindustries.com/i/matched-dir/alpha-banner.webp')
        ->assertSeeTextInOrder([
            sprintf('%d - %s', $newer->cig_id, $newer->title),
            sprintf('%d - %s', $older->cig_id, $older->title),
        ])
        ->assertDontSeeText('No comm-links found for that media URL.');

    assertCommLinkPageLinkPresent($response, route('web.comm-links.show', $newer->cig_id), (string) $newer->cig_id);
    assertCommLinkPageLinkPresent($response, route('web.comm-links.show', $older->cig_id), (string) $older->cig_id);
});
