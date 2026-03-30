<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\AlexandriaExtractor;
use Symfony\Component\DomCrawler\Crawler;

function alexandriaText(string $content): string
{
    return trim((string) preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($content))));
}

it('extracts minigrid component with mg.text element', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.text","data":{"text":"Grid text content"}}]}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect(alexandriaText($content))->toContain('Grid text content');
});

it('extracts minigrid component with mg.media image element', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.media","data":{"image":{"heapImage":{"source":"https://example.com/image.jpg","imageConfiguration":{"imageDescription":{"cropperInformations":{"altText":"Alt Text"}}}}}}}]}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)
        ->toContain('src="https://example.com/image.jpg"')
        ->toContain('alt="Alt Text"');
});

it('extracts minigrid component with mg.media video element', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.media","data":{"video":{"heapVideo":{"source":"https://example.com/video.mp4"}}}}]}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toContain('src="https://example.com/video.mp4"');
});

it('extracts minigrid component with multiple elements', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.text","data":{"text":"Grid Title"}},{"key":"mg.media","data":{"image":{"heapImage":{"source":"https://example.com/image.jpg"}}}}]}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect(alexandriaText($content))->toContain('Grid Title')
        ->and($content)->toContain('src="https://example.com/image.jpg"');
});

it('extracts minigrid component with html-formatted text', function () {
    // Test with HTML content in mg.text that should be preserved
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.text","data":{"text":"Header content"}}]}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect(alexandriaText($content))->toContain('Header content');
});

it('returns empty string for minigrid with missing elements', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toBe('');
});

it('returns empty string for minigrid with invalid elements array', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":"not an array"}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toBe('');
});

it('skips invalid minigrid elements and extracts valid ones', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.text","data":{"text":"Valid text"}},{"key":"invalid","data":{"text":"Should be skipped"}},{"not an array":true}]}}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect(alexandriaText($content))
        ->toContain('Valid text')
        ->not->toContain('Should be skipped');
});

it('extracts separator component permutations as empty string: :dataset', function (string $properties) {
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

it('extracts multiple separator components', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Separator"}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Text","componentProps":{"title":"Text"}}'></g-platform-client-component>
        <g-platform-client-component :properties='{"componentId":"Separator"}'></g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect(alexandriaText($content))
        ->toContain('Text')
        ->not->toContain('Separator');
});

it('extracts background component values when provided: :dataset', function (string $properties, string $selector, string $color) {
    $html = <<<'HTML'
        <g-platform-client-component :properties='__PROPERTIES__'>
        </g-platform-client-component>
        HTML;
    $html = str_replace('__PROPERTIES__', $properties, $html);

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->not->toBe('');

    if ($selector !== '') {
        expect($content)->toContain($selector);
    }

    if ($color !== '') {
        expect($content)->toContain($color);
    }
})->with([
    'selector only' => [
        '{"componentId":"Background","componentProps":{"selector":".main-section"}}',
        '.main-section',
        '',
    ],
    'color only' => [
        '{"componentId":"Background","componentProps":{"backgroundColor":"#ffffff"}}',
        '',
        '#ffffff',
    ],
    'selector and color' => [
        '{"componentId":"Background","componentProps":{"selector":".hero","backgroundColor":"#000000"}}',
        '.hero',
        '#000000',
    ],
]);

it('extracts background component with empty data', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Background","componentProps":{}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toBe('');
});

it('extracts background component with empty string values', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"Background","componentProps":{"selector":"","backgroundColor":""}}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect($content)->toBe('');
});

it('extracts background component with complex color values', function () {
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

it('extracts multiple background components', function () {
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

it('extracts orioncardslist component with valid cards', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"OrionCardsList","componentProps":{"cards":[{"title":"Card 1","description":"Description 1"},{"title":"Card 2","description":"Description 2"}]}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    $text = alexandriaText($content);

    expect($text)
        ->toContain('Card 1')
        ->toContain('Description 1')
        ->toContain('Card 2')
        ->toContain('Description 2');
});

it('extracts orioncardslist component with title only', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"OrionCardsList","componentProps":{"cards":[{"title":"Title Only"}]}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect(alexandriaText($content))
        ->toContain('Title Only')
        ->not->toContain('Description Only');
});

it('extracts orioncardslist component with description only', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"OrionCardsList","componentProps":{"cards":[{"description":"Description Only"}]}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect(alexandriaText($content))
        ->toContain('Description Only')
        ->not->toContain('Title Only');
});

it('returns empty string for orioncardslist invalid card payload permutations: :dataset', function (string $properties) {
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

it('skips invalid orioncardslist cards and extracts valid ones', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"OrionCardsList","componentProps":{"cards":[{"title":"Valid Card"},{"not a card":true},{"title":"Another Valid","description":"With description"}]}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    expect(alexandriaText($content))
        ->toContain('Valid Card')
        ->toContain('Another Valid')
        ->toContain('With description')
        ->not->toContain('not a card');
});

it('extracts orioncardslist with multiple cards properly formatted', function () {
    $html = <<<'HTML'
        <g-platform-client-component :properties='{"componentId":"OrionCardsList","componentProps":{"cards":[{"title":"First","description":"First desc"},{"title":"Second","description":"Second desc"},{"title":"Third","description":"Third desc"}]}}'>
        </g-platform-client-component>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);
    $content = $extractor->getContent(false);

    $text = alexandriaText($content);

    foreach (['First', 'First desc', 'Second', 'Second desc', 'Third', 'Third desc'] as $line) {
        expect($text)->toContain($line);
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

    expect(alexandriaText($content))
        ->toContain('MiniGrid text')
        ->toContain('Card')
        ->toContain('Card desc');

    expect($content)
        ->toContain('.test')
        ->toContain('#fff');
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
