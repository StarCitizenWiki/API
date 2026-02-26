<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\AlexandriaExtractor;
use Symfony\Component\DomCrawler\Crawler;

it('extracts MiniGrid component with mg.text element', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.text","data":{"text":"Grid text content"}}]}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('Grid text content');
});

it('extracts MiniGrid component with mg.media image element', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.media","data":{"image":{"heapImage":{"source":"https://example.com/image.jpg","imageConfiguration":{"imageDescription":{"cropperInformations":{"altText":"Alt Text"}}}}}}}]}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<img src="https://example.com/image.jpg" alt="Alt Text" />');
});

it('extracts MiniGrid component with mg.media video element', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.media","data":{"video":{"heapVideo":{"source":"https://example.com/video.mp4"}}}}]}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<video src="https://example.com/video.mp4"></video>');
});

it('extracts MiniGrid component with multiple elements', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.text","data":{"text":"Grid Title"}},{"key":"mg.media","data":{"image":{"heapImage":{"source":"https://example.com/image.jpg"}}}}]}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('Grid Title')
        ->toContain('<img src="https://example.com/image.jpg" alt="" />');
});

it('extracts MiniGrid component with HTML-formatted text', function () {
    // Test with HTML content in mg.text that should be preserved
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.text","data":{"text":"Header content"}}]}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('Header content');
});

it('returns empty string for MiniGrid with missing elements', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toBe('');
});

it('returns empty string for MiniGrid with invalid elements array', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":"not an array"}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toBe('');
});

it('skips invalid MiniGrid elements and extracts valid ones', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.text","data":{"text":"Valid text"}},{"key":"invalid","data":{"text":"Should be skipped"}},{"not an array":true}]}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('Valid text')
        ->not->toContain('Should be skipped');
});

it('extracts Separator component permutations as empty string: :dataset', function (string $properties) {
    $html = <<<'HTML'
        <g-platform-client-component :properties='__PROPERTIES__'>
        </g-platform-client-component>
        HTML;
    $html = str_replace('__PROPERTIES__', $properties, $html);

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toBe('');
})->with([
    'with props' => ['{"componentId":"Separator","componentProps":{"any":"data"}}'],
    'with empty props' => ['{"componentId":"Separator","componentProps":{}}'],
    'with no props' => ['{"componentId":"Separator"}'],
    'with complex props' => ['{"componentId":"Separator","componentProps":{"height":"10px","color":"#000","style":"dashed"}}'],
    'with nested arrays' => ['{"componentId":"Separator","componentProps":{"nested":{"deeply":{"data":"value"}}}}'],
]);

it('extracts multiple Separator components', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Separator"}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Text","componentProps":{"title":"Text"}}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Separator"}'></g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<h2>Text</h2>')
        ->not->toContain('Separator');
});

it('extracts Background component with simple prop permutations: :dataset', function (string $properties, string $expected) {
    $html = <<<'HTML'
        <g-platform-client-component :properties='__PROPERTIES__'>
        </g-platform-client-component>
        HTML;
    $html = str_replace('__PROPERTIES__', $properties, $html);

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toBe($expected);
})->with([
    'selector only' => [
        '{"componentId":"Background","componentProps":{"selector":".main-section"}}',
        '<!-- Background: selector=.main-section color= -->',
    ],
    'color only' => [
        '{"componentId":"Background","componentProps":{"backgroundColor":"#ffffff"}}',
        '<!-- Background: selector= color=#ffffff -->',
    ],
    'selector and color' => [
        '{"componentId":"Background","componentProps":{"selector":".hero","backgroundColor":"#000000"}}',
        '<!-- Background: selector=.hero color=#000000 -->',
    ],
]);

it('extracts Background component with empty data', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Background","componentProps":{}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toBe('');
});

it('extracts Background component with empty string values', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Background","componentProps":{"selector":"","backgroundColor":""}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toBe('');
});

it('extracts Background component with complex color values', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Background","componentProps":{"selector":".gradient","backgroundColor":"linear-gradient(to right, #ff0000, #0000ff)"}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toContain('selector=.gradient')
        ->toContain('linear-gradient(to right, #ff0000, #0000ff)');
});

it('extracts multiple Background components', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Background","componentProps":{"selector":".header","backgroundColor":"#333"}}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Text","componentProps":{"title":"Content"}}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Background","componentProps":{"selector":".footer","backgroundColor":"#111"}}'></g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('selector=.header')
        ->toContain('color=#333')
        ->toContain('<h2>Content</h2>')
        ->toContain('selector=.footer')
        ->toContain('color=#111');
});

it('extracts OrionCardsList component with valid cards', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"OrionCardsList","componentProps":{"cards":[{"title":"Card 1","description":"Description 1"},{"title":"Card 2","description":"Description 2"}]}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<h3>Card 1</h3>')
        ->toContain('<p>Description 1</p>')
        ->toContain('<h3>Card 2</h3>')
        ->toContain('<p>Description 2</p>');
});

it('extracts OrionCardsList component with title only', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"OrionCardsList","componentProps":{"cards":[{"title":"Title Only"}]}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<h3>Title Only</h3>')
        ->not->toContain('<p>');
});

it('extracts OrionCardsList component with description only', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"OrionCardsList","componentProps":{"cards":[{"description":"Description Only"}]}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<p>Description Only</p>')
        ->not->toContain('<h3>');
});

it('returns empty string for OrionCardsList invalid card payload permutations: :dataset', function (string $properties) {
    $html = <<<'HTML'
        <g-platform-client-component :properties='__PROPERTIES__'>
        </g-platform-client-component>
        HTML;
    $html = str_replace('__PROPERTIES__', $properties, $html);

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toBe('');
})->with([
    'empty cards array' => ['{"componentId":"OrionCardsList","componentProps":{"cards":[]}}'],
    'missing cards' => ['{"componentId":"OrionCardsList","componentProps":{}}'],
    'invalid cards type' => ['{"componentId":"OrionCardsList","componentProps":{"cards":"not an array"}}'],
]);

it('skips invalid OrionCardsList cards and extracts valid ones', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"OrionCardsList","componentProps":{"cards":[{"title":"Valid Card"},{"not a card":true},{"title":"Another Valid","description":"With description"}]}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('<h3>Valid Card</h3>')
        ->toContain('<h3>Another Valid</h3>')
        ->toContain('<p>With description</p>')
        ->not->toContain('not a card');
});

it('extracts OrionCardsList with multiple cards properly formatted', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"OrionCardsList","componentProps":{"cards":[{"title":"First","description":"First desc"},{"title":"Second","description":"Second desc"},{"title":"Third","description":"Third desc"}]}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    $expectedLines = [
        '<h3>First</h3>',
        '<p>First desc</p>',
        '<h3>Second</h3>',
        '<p>Second desc</p>',
        '<h3>Third</h3>',
        '<p>Third desc</p>',
    ];

    foreach ($expectedLines as $line) {
        expect($content)->toContain($line);
    }
});

it('extracts all four new component types together', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.text","data":{"text":"MiniGrid text"}}]}}}}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Separator"}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Background","componentProps":{"selector":".test","backgroundColor":"#fff"}}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"OrionCardsList","componentProps":{"cards":[{"title":"Card","description":"Card desc"}]}}'></g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('MiniGrid text')
        ->toContain('selector=.test')
        ->toContain('color=#fff')
        ->toContain('<h3>Card</h3>')
        ->toContain('<p>Card desc</p>');
});

it('handles new components with missing or malformed data gracefully', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid"}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"OrionCardsList"}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Background"}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Separator"}'></g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toBe('');
});
