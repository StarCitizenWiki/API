<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\Traits\GHeaderExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

function headerExtractor(): object
{
    return new class
    {
        use GHeaderExtractorTrait;
    };
}

function headerText(string $markup): string
{
    return trim((string) preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($markup))));
}

it('extracts the title and content slots from a g-header element', function (): void {
    $result = headerExtractor()->getHeader(new Crawler(<<<'HTML'
        <g-header :background-options="{&quot;isTransparent&quot;:false}">
          <template slot="title">IN-GAME REWARDS (FLAIR)</template>
          <template slot="content">
            <p>The 'verse is full of dangerous outlaws and questionable characters.</p>
          </template>
        </g-header>
        HTML));

    expect(headerText($result))->toContain('IN-GAME REWARDS (FLAIR)')
        ->and(headerText($result))->toContain("The 'verse is full of dangerous outlaws and questionable characters.");
});

it('keeps multiple headers in the output', function (): void {
    $result = headerExtractor()->getHeader(new Crawler(<<<'HTML'
        <g-header>
          <template slot="title">First Title</template>
          <template slot="content"><p>First Content</p></template>
        </g-header>
        <g-header>
          <template slot="title">Second Title</template>
          <template slot="content"><p>Second Content</p></template>
        </g-header>
        HTML));

    $text = headerText($result);

    expect(substr_count($text, 'First Title'))->toBe(1)
        ->and(substr_count($text, 'Second Title'))->toBe(1)
        ->and(substr_count($text, 'First Content'))->toBe(1)
        ->and(substr_count($text, 'Second Content'))->toBe(1);
});

it('handles missing title and content slots independently', function (string $markup, string $expected, string $unexpected): void {
    $result = headerExtractor()->getHeader(new Crawler($markup));

    $text = headerText($result);

    expect($text)->toContain($expected)
        ->and($text)->not->toContain($unexpected);
})->with([
    'missing title slot' => [
        <<<'HTML'
        <g-header :background-options="{}">
          <template slot="content">
            <p>Content without title</p>
          </template>
        </g-header>
        HTML,
        'Content without title',
        'Title Only',
    ],
    'missing content slot' => [
        <<<'HTML'
        <g-header :background-options="{}">
          <template slot="title">Title Only</template>
        </g-header>
        HTML,
        'Title Only',
        'Content without title',
    ],
]);

it('returns an empty string when both slots are missing', function (): void {
    expect(headerExtractor()->getHeader(new Crawler(<<<'HTML'
        <g-header :background-options="{}"></g-header>
        HTML)))->toBe('');
});

it('returns an empty string when no g-header element is present', function (): void {
    expect(headerExtractor()->getHeader(new Crawler('<p>No header here</p>')))->toBe('');
});
