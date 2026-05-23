<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\Traits\GNarrativeGroupExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

function narrativeGroupExtractor(): object
{
    return new class
    {
        use GNarrativeGroupExtractorTrait;
    };
}

it('extracts article and illustration content from a narrative group in order', function (): void {
    $result = narrativeGroupExtractor()->getNarrativeGroup(new Crawler(<<<'HTML'
        <g-narrative-group :background-options='{"isTransparent":false}'>
          <g-article headline="Article Title" byline="Author Name" body="<p>Body content</p>" />
          <g-illustration sign-intro="Illustration by " sign-name="Artist" sign-link-href="https://example.test/artist" :simple-image='{"desktop":"/i/test.png"}' />
        </g-narrative-group>
        HTML));

    expect($result)->toContain('<h1>Article Title</h1>')
        ->and($result)->toContain('<p>Author Name<br /></p>')
        ->and($result)->toContain('<p>Body content</p>')
        ->and($result)->toContain('<p class="illustration-credit">Illustration by </p>')
        ->and($result)->toContain('<a href="https://example.test/artist">Artist</a>')
        ->and($result)->not->toContain('isTransparent')
        ->and($result)->not->toContain('/i/test.png');
});

it('keeps narrative items separated and supports unlinked illustrations', function (): void {
    $result = narrativeGroupExtractor()->getNarrativeGroup(new Crawler(<<<'HTML'
        <g-narrative-group :background-options='{"isTransparent":false,"complex":"data"}'>
          <g-article headline="First Article" byline="Author 1" body="<p>Content 1</p>" />
          <g-article headline="Second Article" body="<p>Content 2</p>" />
          <g-illustration sign-intro="By " sign-name="Artist 1" :simple-image='{}' />
        </g-narrative-group>
        HTML));

    expect($result)->toContain("<h1>First Article</h1>\n<p>Author 1<br /></p>\n<p>Content 1</p>\n<h1>Second Article</h1>")
        ->and($result)->toContain('<p class="illustration-credit">By </p>')
        ->and($result)->toContain('<p>Artist 1</p>')
        ->and($result)->not->toContain('<a href=');
});

it('returns an empty string when no narrative group is present', function (): void {
    expect(narrativeGroupExtractor()->getNarrativeGroup(new Crawler('<p>No narrative group here</p>')))->toBe('');
});
