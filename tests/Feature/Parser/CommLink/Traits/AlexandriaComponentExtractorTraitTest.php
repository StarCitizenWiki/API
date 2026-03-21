<?php

declare(strict_types=1);

namespace Tests\Feature\Parser\CommLink\Traits;

use App\Services\Parser\CommLink\Content\Traits\AlexandriaComponentExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

it('extracts text component with all props', function (): void {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-platform-client-component :properties='{"componentId":"Text","componentProps":{"text":"Body text","title":"Heading","description":"Subtitle"}}'></g-platform-client-component>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use AlexandriaComponentExtractorTrait;
    };

    $result = $extractor->getAlexandriaComponents($crawler);

    expect($result)->toContain('<p>Body text</p>');
    expect($result)->toContain('<h2>Heading</h2>');
    expect($result)->toContain('<p>Subtitle</p>');
});

it('extracts text component with missing props gracefully', function (): void {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-platform-client-component :properties='{"componentId":"Text","componentProps":{"title":"Only title"}}'></g-platform-client-component>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use AlexandriaComponentExtractorTrait;
    };

    $result = $extractor->getAlexandriaComponents($crawler);

    expect($result)->toBe('<h2>Only title</h2>');
});

it('extracts image component', function (): void {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-platform-client-component :properties='{"componentId":"Image","componentProps":{"altText":"Alt text","caption":"Caption","title":"Title"}}'></g-platform-client-component>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use AlexandriaComponentExtractorTrait;
    };

    $result = $extractor->getAlexandriaComponents($crawler);

    expect($result)->toContain('<figure>');
    expect($result)->toContain('<img alt="Alt text" />');
    expect($result)->toContain('<figcaption>Caption - Title</figcaption>');
    expect($result)->toContain('</figure>');
});

it('extracts video component', function (): void {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-platform-client-component :properties='{"componentId":"Video","componentProps":{"title":"Video title","description":"Video description"}}'></g-platform-client-component>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use AlexandriaComponentExtractorTrait;
    };

    $result = $extractor->getAlexandriaComponents($crawler);

    expect($result)->toContain('<h2>Video title</h2>');
    expect($result)->toContain('<p>Video description</p>');
});

it('extracts quote component', function (): void {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-platform-client-component :properties='{"componentId":"Quote","componentProps":{"text":"Quote text","author":"Author","source":"Source"}}'></g-platform-client-component>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use AlexandriaComponentExtractorTrait;
    };

    $result = $extractor->getAlexandriaComponents($crawler);

    expect($result)->toBe('<blockquote><p>Quote text</p><cite>Author, Source</cite></blockquote>');
});

it('extracts gallery component', function (): void {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-platform-client-component :properties='{"componentId":"Gallery","componentProps":{"title":"Gallery title","items":["One","Two"]}}'></g-platform-client-component>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use AlexandriaComponentExtractorTrait;
    };

    $result = $extractor->getAlexandriaComponents($crawler);

    expect($result)->toContain('<h2>Gallery title</h2>');
    expect($result)->toContain('<ul>');
    expect($result)->toContain('<li>One</li>');
    expect($result)->toContain('<li>Two</li>');
    expect($result)->toContain('</ul>');
});

it('extracts button component', function (): void {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-platform-client-component :properties='{"componentId":"Button","componentProps":{"label":"Click me"}}'></g-platform-client-component>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use AlexandriaComponentExtractorTrait;
    };

    $result = $extractor->getAlexandriaComponents($crawler);

    expect($result)->toBe('<button>Click me</button>');
});

it('extracts call-to-action component', function (): void {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-platform-client-component :properties='{"componentId":"CallToAction","componentProps":{"title":"CTA title","description":"CTA description","buttonLabel":"Go"}}'></g-platform-client-component>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use AlexandriaComponentExtractorTrait;
    };

    $result = $extractor->getAlexandriaComponents($crawler);

    expect($result)->toContain('<h2>CTA title</h2>');
    expect($result)->toContain('<p>CTA description</p>');
    expect($result)->toContain('<button>Go</button>');
});

it('returns empty string for invalid component payload permutations: :dataset', function (string $componentMarkup): void {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
__COMPONENT_MARKUP__
</body>
</html>
HTML;
    $html = str_replace('__COMPONENT_MARKUP__', $componentMarkup, $html);

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use AlexandriaComponentExtractorTrait;
    };

    $result = $extractor->getAlexandriaComponents($crawler);

    expect($result)->toBe('');
})->with([
    'unknown component type' => ['<g-platform-client-component :properties=\'{"componentId":"Unknown","componentProps":{}}\'></g-platform-client-component>'],
    'invalid json' => ['<g-platform-client-component :properties=\'invalid json\'></g-platform-client-component>'],
    'missing :properties attribute' => ['<g-platform-client-component></g-platform-client-component>'],
]);
