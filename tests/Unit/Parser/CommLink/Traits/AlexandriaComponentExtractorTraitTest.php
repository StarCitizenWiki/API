<?php

declare(strict_types=1);

namespace Tests\Unit\Parser\CommLink\Traits;

use App\Services\Parser\CommLink\Content\Traits\AlexandriaComponentExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

function alexandriaComponentExtractor(): object
{
    return new class
    {
        use AlexandriaComponentExtractorTrait;
    };
}

function alexandriaExtractedText(string $content): string
{
    return trim((string) preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($content))));
}

it('extracts representative Alexandria components', function (string $markup, array $expectedFragments): void {
    $result = alexandriaComponentExtractor()->getAlexandriaComponents(new Crawler($markup));
    $text = alexandriaExtractedText($result);

    foreach ($expectedFragments as $fragment) {
        expect($text)->toContain($fragment);
    }
})->with([
    'text' => [
        <<<'HTML'
            <g-platform-client-component :properties='{"componentId":"Text","componentProps":{"text":"Body text","title":"Heading","description":"Subtitle"}}'></g-platform-client-component>
            HTML,
        [
            'Body text',
            'Heading',
            'Subtitle',
        ],
    ],
    'image' => [
        <<<'HTML'
            <g-platform-client-component :properties='{"componentId":"Image","componentProps":{"altText":"Alt text","caption":"Caption","title":"Title"}}'></g-platform-client-component>
            HTML,
        [
            'Caption - Title',
        ],
    ],
    'video' => [
        <<<'HTML'
            <g-platform-client-component :properties='{"componentId":"Video","componentProps":{"title":"Video title","description":"Video description"}}'></g-platform-client-component>
            HTML,
        [
            'Video title',
            'Video description',
        ],
    ],
    'quote' => [
        <<<'HTML'
            <g-platform-client-component :properties='{"componentId":"Quote","componentProps":{"text":"Quote text","author":"Author","source":"Source"}}'></g-platform-client-component>
            HTML,
        [
            'Quote text',
            'Author, Source',
        ],
    ],
    'gallery' => [
        <<<'HTML'
            <g-platform-client-component :properties='{"componentId":"Gallery","componentProps":{"title":"Gallery title","items":["One","Two"]}}'></g-platform-client-component>
            HTML,
        [
            'Gallery title',
            'One',
            'Two',
        ],
    ],
    'button' => [
        <<<'HTML'
            <g-platform-client-component :properties='{"componentId":"Button","componentProps":{"label":"Click me"}}'></g-platform-client-component>
            HTML,
        [
            'Click me',
        ],
    ],
    'call to action' => [
        <<<'HTML'
            <g-platform-client-component :properties='{"componentId":"CallToAction","componentProps":{"title":"CTA title","description":"CTA description","buttonLabel":"Go"}}'></g-platform-client-component>
            HTML,
        [
            'CTA title',
            'CTA description',
            'Go',
        ],
    ],
]);

it('extracts MiniGrid, Separator, Background, and Orion cards list content', function (): void {
    $result = alexandriaComponentExtractor()->getAlexandriaComponents(new Crawler(<<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.text","data":{"text":"MiniGrid text"}},{"key":"mg.media","data":{"image":{"heapImage":{"source":"https://example.com/image.jpg","imageConfiguration":{"imageDescription":{"cropperInformations":{"altText":"Alt Text"}}}}}}},{"key":"mg.media","data":{"video":{"heapVideo":{"source":"https://example.com/video.mp4"}}}}]}}}}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Separator"}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Background","componentProps":{"selector":".test","backgroundColor":"#fff"}}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"OrionCardsList","componentProps":{"cards":[{"title":"Card 1","description":"Description 1"},{"title":"Card 2","description":"Description 2"}]}}'></g-platform-client-component>
        HTML));

    expect(alexandriaExtractedText($result))->toContain('MiniGrid text')
        ->and(alexandriaExtractedText($result))->toContain('Card 1')
        ->and(alexandriaExtractedText($result))->toContain('Description 1')
        ->and(alexandriaExtractedText($result))->toContain('Card 2')
        ->and(alexandriaExtractedText($result))->toContain('Description 2')
        ->and($result)->toContain('src="https://example.com/image.jpg"')
        ->and($result)->toContain('alt="Alt Text"')
        ->and($result)->toContain('src="https://example.com/video.mp4"')
        ->and($result)->toContain('.test')
        ->and($result)->toContain('#fff')
        ->and($result)->not->toContain('Separator');
});

it('returns an empty string for unsupported or invalid component payloads', function (string $markup): void {
    $result = alexandriaComponentExtractor()->getAlexandriaComponents(new Crawler($markup));

    expect($result)->toBe('');
})->with([
    'unknown component type' => ['<g-platform-client-component :properties=\'{"componentId":"Unknown","componentProps":{}}\'></g-platform-client-component>'],
    'invalid json' => ['<g-platform-client-component :properties=\'invalid json\'></g-platform-client-component>'],
    'missing properties attribute' => ['<g-platform-client-component></g-platform-client-component>'],
]);
