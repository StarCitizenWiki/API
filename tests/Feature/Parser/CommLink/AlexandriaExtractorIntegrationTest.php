<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\AlexandriaExtractor;
use Symfony\Component\DomCrawler\Crawler;

it('extracts single component permutations from HTML: :dataset', function (string $properties, array $expectedContent): void {
    $html = <<<'HTML'
        <g-platform-client-component :properties='__PROPERTIES__'>
        </g-platform-client-component>
        HTML;
    $html = str_replace('__PROPERTIES__', $properties, $html);

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    foreach ($expectedContent as $expectedLine) {
        expect($content)->toContain($expectedLine);
    }
})->with([
    'Text component' => [
        '{"componentId":"Text","componentProps":{"title":"Test Title","text":"Test Text"}}',
        ['<h2>Test Title</h2>', '<p>Test Text</p>'],
    ],
    'Image component' => [
        '{"componentId":"Image","componentProps":{"altText":"Test Alt","caption":"Test Caption"}}',
        ['<figure>', '<img alt="Test Alt" />', '<figcaption>Test Caption</figcaption>'],
    ],
    'Video component' => [
        '{"componentId":"Video","componentProps":{"title":"Video Title","description":"Video Description"}}',
        ['<h2>Video Title</h2>', '<p>Video Description</p>'],
    ],
    'Quote component' => [
        '{"componentId":"Quote","componentProps":{"text":"Quote Text","author":"Author Name","source":"Source Name"}}',
        ['<blockquote>', '<p>Quote Text</p>', '<cite>Author Name, Source Name</cite>'],
    ],
    'Gallery component' => [
        '{"componentId":"Gallery","componentProps":{"title":"Gallery Title","items":["Item 1","Item 2","Item 3"]}}',
        ['<h2>Gallery Title</h2>', '<ul>', '<li>Item 1</li>', '<li>Item 2</li>', '<li>Item 3</li>'],
    ],
    'Button component' => [
        '{"componentId":"Button","componentProps":{"label":"Click Me"}}',
        ['<button>Click Me</button>'],
    ],
    'CallToAction component' => [
        '{"componentId":"CallToAction","componentProps":{"title":"CTA Title","description":"CTA Description","buttonLabel":"Action"}}',
        ['<h2>CTA Title</h2>', '<p>CTA Description</p>', '<button>Action</button>'],
    ],
    'Text backward compatibility' => [
        '{"componentId":"Text","componentProps":{"title":"Text Title","text":"Text Content","description":"Text Description"}}',
        ['<h2>Text Title</h2>', '<p>Text Content</p>', '<p>Text Description</p>'],
    ],
]);

it('extracts multiple component types from same HTML', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Text","componentProps":{"title":"First Title","text":"First Text"}}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Image","componentProps":{"altText":"Image Alt"}}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Quote","componentProps":{"text":"Quote Text"}}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Gallery","componentProps":{"items":["Item 1","Item 2"]}}'></g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<h2>First Title</h2>')
        ->toContain('<p>First Text</p>')
        ->toContain('<img alt="Image Alt" />')
        ->toContain('<blockquote>')
        ->toContain('<ul>')
        ->toContain('<li>Item 1</li>')
        ->toContain('<li>Item 2</li>');
});

it('returns empty string for unknown component types', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"UnknownComponent","componentProps":{"data":"some data"}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toBe('');
});

it('combines content from all extractors', function () {
    $html = <<<'HTML'
        <g-introduction :info='{"title":"Intro Title","contents":"Intro Description"}'></g-introduction>
        <g-platform-client-component :properties='{"componentId":"Text","componentProps":{"title":"Text Title","text":"Text Content"}}'></g-platform-client-component>
        <g-banner-advanced :content='{"text":{"title":"Banner Title"}}'></g-banner-advanced>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent();

    expect($content)
        ->toContain('Intro Title')
        ->toContain('Intro Description')
        ->toContain('<h2>Text Title</h2>')
        ->toContain('<p>Text Content</p>')
        ->toContain('Banner Title');
});

it('handles g-navigation-sales extraction', function () {
    $html = <<<'HTML'
        <g-navigation-sales :navigation='{"items":[{"label":"Item 1"},{"label":"Item 2"},{"label":"Item 3"}]}'>
        </g-navigation-sales>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent();

    expect($content)
        ->toContain('<ul>')
        ->toContain('<li>Item 1</li>')
        ->toContain('<li>Item 2</li>')
        ->toContain('<li>Item 3</li>');
});
