<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\AlexandriaExtractor;
use Symfony\Component\DomCrawler\Crawler;

it('extracts Text component from HTML', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Text","componentProps":{"title":"Test Title","text":"Test Text"}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<h2>Test Title</h2>')
        ->toContain('<p>Test Text</p>');
});

it('extracts Image component from HTML', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Image","componentProps":{"altText":"Test Alt","caption":"Test Caption"}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<figure>')
        ->toContain('<img alt="Test Alt" />')
        ->toContain('<figcaption>Test Caption</figcaption>');
});

it('extracts Video component from HTML', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Video","componentProps":{"title":"Video Title","description":"Video Description"}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<h2>Video Title</h2>')
        ->toContain('<p>Video Description</p>');
});

it('extracts Quote component from HTML', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Quote","componentProps":{"text":"Quote Text","author":"Author Name","source":"Source Name"}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<blockquote>')
        ->toContain('<p>Quote Text</p>')
        ->toContain('<cite>Author Name, Source Name</cite>');
});

it('extracts Gallery component from HTML', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Gallery","componentProps":{"title":"Gallery Title","items":["Item 1","Item 2","Item 3"]}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<h2>Gallery Title</h2>')
        ->toContain('<ul>')
        ->toContain('<li>Item 1</li>')
        ->toContain('<li>Item 2</li>')
        ->toContain('<li>Item 3</li>');
});

it('extracts Button component from HTML', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Button","componentProps":{"label":"Click Me"}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<button>Click Me</button>');
});

it('extracts CallToAction component from HTML', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"CallToAction","componentProps":{"title":"CTA Title","description":"CTA Description","buttonLabel":"Action"}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<h2>CTA Title</h2>')
        ->toContain('<p>CTA Description</p>')
        ->toContain('<button>Action</button>');
});

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

it('maintains backward compatibility with Text component behavior', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Text","componentProps":{"title":"Text Title","text":"Text Content","description":"Text Description"}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<h2>Text Title</h2>')
        ->toContain('<p>Text Content</p>')
        ->toContain('<p>Text Description</p>');
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
