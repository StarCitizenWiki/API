<?php

declare(strict_types=1);

namespace Tests\Unit\Parser\CommLink;

use App\Services\Parser\CommLink\Content\AlexandriaExtractor;
use App\Services\Parser\CommLink\Content\DefaultExtractor;
use App\Services\Parser\CommLink\Content\LayoutSystemExtractor;
use App\Services\Parser\CommLink\Content\UniversalContentExtractor;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

function parsedText(string $content): string
{
    return trim((string) preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($content))));
}

function assertStringsAppearInOrder(string $content, array $needles): void
{
    $offset = 0;

    foreach ($needles as $needle) {
        $position = strpos($content, $needle, $offset);

        expect($position)->not->toBeFalse();

        $offset = $position + strlen($needle);
    }
}

it('extracts representative universal content and preserves fallback text', function (): void {
    $extractor = new UniversalContentExtractor(new Crawler(<<<'HTML'
        <div>
            <g-introduction :info='{"title":"Intro Title","subtitle":"Intro Subtitle","contents":["Body line 1","Body line 2"]}'></g-introduction>
            <g-banner-advanced :content='{"text":{"title":"Banner Title","subtitle":"Banner Subtitle","paragraph":"Banner paragraph"}}'></g-banner-advanced>
            <g-feature :is-header-declared="true">
                <template slot="title">Feature Title</template>
                <template slot="subtitle">Feature Subtitle</template>
            </g-feature>
            <g-unknown-element>Fallback text</g-unknown-element>
        </div>
        HTML));

    $content = $extractor->getContent();
    $text = parsedText($content);

    expect($text)->toContain('Intro Title')
        ->and($text)->toContain('Intro Subtitle')
        ->and($text)->toContain('Body line 1')
        ->and($text)->toContain('Banner Title')
        ->and($text)->toContain('Banner paragraph')
        ->and($text)->toContain('Feature Title')
        ->and($text)->toContain('Feature Subtitle')
        ->and($text)->toContain('Fallback text');
});

it('handles universal extractor edge cases', function (): void {
    expect((new UniversalContentExtractor(new Crawler('')))->getContent())->toBe('');

    expect(parsedText((new UniversalContentExtractor(new Crawler('<div>Standard HTML</div><g-introduction :info=\'{"title":"Intro"}\'></g-introduction>')))->getContent()))
        ->toContain('Intro');

    Log::shouldReceive('warning')
        ->once()
        ->with('No extractor for <g-unknown-element>');

    expect(parsedText((new UniversalContentExtractor(new Crawler('<g-unknown-element>Unknown Content</g-unknown-element>')))->getContent()))->toBe('Unknown Content');
});

it('extracts alexandria content in the expected order', function (): void {
    $extractor = new AlexandriaExtractor(new Crawler(<<<'HTML'
        <div>
            <g-platform-client-component :properties='{"componentId":"Text","componentProps":{"text":"Body text","title":"Heading","description":"Subtitle"}}'></g-platform-client-component>
            <g-feature :is-header-declared="true">
                <template slot="title">Feature Title</template>
                <template slot="subtitle">Feature Subtitle</template>
            </g-feature>
            <g-navigation-sales :navigation='{"items":[{"label":"First"},{"label":"Second"}]}'></g-navigation-sales>
        </div>
        HTML));

    $content = $extractor->getContent();

    assertStringsAppearInOrder(parsedText($content), [
        'Body text',
        'Heading',
        'Feature Title',
        'Feature Subtitle',
        'First',
        'Second',
    ]);
});

it('extracts default segment content and the introduction', function (): void {
    $extractor = new DefaultExtractor(new Crawler(<<<'HTML'
        <div>
            <g-introduction :info='{"title":"Introduction"}'></g-introduction>
            <div class="segment">
                <p>Segment body</p>
            </div>
        </div>
        HTML));

    $content = $extractor->getContent();

    expect(parsedText($content))->toContain('Introduction')
        ->and(parsedText($content))->toContain('Segment body');
});

it('extracts layout-system content and strips extracted g-elements', function (): void {
    $extractor = new LayoutSystemExtractor(new Crawler(<<<'HTML'
        <div id="layout-system">
            <g-introduction :info='{"title":"Layout Title","contents":["Layout intro"]}'></g-introduction>
            <div class="content">Layout body</div>
        </div>
        HTML));

    $content = $extractor->getContent();

    expect(parsedText($content))->toContain('Layout Title')
        ->and(parsedText($content))->toContain('Layout intro')
        ->and(parsedText($content))->toContain('Layout body')
        ->and($content)->not->toContain('<g-introduction')
        ->and($content)->not->toContain('id="layout-system"');
});
