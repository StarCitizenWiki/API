<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\AlexandriaExtractor;
use App\Services\Parser\CommLink\Content\ContentExtractorFactory;
use App\Services\Parser\CommLink\Content\DefaultExtractor;
use App\Services\Parser\CommLink\Content\LayoutSystemExtractor;
use App\Services\Parser\CommLink\Content\UniversalContentExtractor;
use App\Services\Parser\CommLink\Content\VueArticleExtractor;
use Symfony\Component\DomCrawler\Crawler;

it('selects universalcontentextractor for html with g- elements', function () {
    $html = <<<'HTML'
<html>
<body>
    <g-introduction>Test Introduction</g-introduction>
    <g-banner-advanced>
        <g-banner-text>Advanced Banner</g-banner-text>
    </g-banner-advanced>
    <g-explore>Explore Content</g-explore>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = ContentExtractorFactory::getParserFromCrawler($crawler);

    expect($extractor)->toBeInstanceOf(UniversalContentExtractor::class);
});

it('selects layoutsystemextractor for html with layout-system', function () {
    $html = <<<'HTML'
<html>
<body>
    <div id="layout-system">
        <div class="content">Layout System Content</div>
    </div>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = ContentExtractorFactory::getParserFromCrawler($crawler);

    expect($extractor)->toBeInstanceOf(LayoutSystemExtractor::class);
});

it('selects universalcontentextractor for html with alexandria g-platform-client-component', function () {
    $html = <<<'HTML'
<html>
<body>
    <g-platform-client-component>
        <div>Alexandria Component</div>
    </g-platform-client-component>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = ContentExtractorFactory::getParserFromCrawler($crawler);

    expect($extractor)->toBeInstanceOf(UniversalContentExtractor::class);
});

it('selects universalcontentextractor for html with g-article', function () {
    $html = <<<'HTML'
<html>
<body>
    <g-article headline="Test Headline" byline="Test Byline" body="Test Body">
    </g-article>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = ContentExtractorFactory::getParserFromCrawler($crawler);

    expect($extractor)->toBeInstanceOf(UniversalContentExtractor::class);
});

it('selects defaultextractor for html with .segment', function () {
    $html = <<<'HTML'
<html>
<body>
    <div class="segment">Default Segment Content</div>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = ContentExtractorFactory::getParserFromCrawler($crawler);

    expect($extractor)->toBeInstanceOf(DefaultExtractor::class);
});

it('selects universalcontentextractor for html with g-feature', function () {
    $html = <<<'HTML'
<html>
<body>
    <g-feature>
        <div>Feature Content</div>
    </g-feature>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = ContentExtractorFactory::getParserFromCrawler($crawler);

    expect($extractor)->toBeInstanceOf(UniversalContentExtractor::class);
});

it('universal content extractor has highest priority over layout-system', function () {
    $html = <<<'HTML'
<html>
<body>
    <g-introduction>Test Introduction</g-introduction>
    <div id="layout-system">
        <div class="content">Layout System Content</div>
    </div>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = ContentExtractorFactory::getParserFromCrawler($crawler);

    expect($extractor)->toBeInstanceOf(UniversalContentExtractor::class);
});

it('returns null for empty crawler', function () {
    $html = '<html><body></body></html>';

    $crawler = new Crawler($html);

    $extractor = ContentExtractorFactory::getParserFromCrawler($crawler);

    expect($extractor)->toBeNull();
});

it('universal content extractor can parse returns true for g- elements', function () {
    $html = <<<'HTML'
<html>
<body>
    <g-introduction>Test Introduction</g-introduction>
    <g-explore>Explore Content</g-explore>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $canParse = UniversalContentExtractor::canParse($crawler);

    expect($canParse[0])->toBeTrue();
    expect($canParse[1])->toBe(PHP_INT_MAX);
});

it('layout system extractor can parse returns correct priority', function () {
    $html = <<<'HTML'
<html>
<body>
    <div id="layout-system">
        <div class="content">Layout System Content</div>
    </div>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $canParse = LayoutSystemExtractor::canParse($crawler);

    expect($canParse[0])->toBeTrue();
    expect($canParse[1])->toBe(11);
});

it('alexandria extractor can parse returns correct priority', function () {
    $html = <<<'HTML'
<html>
<body>
    <g-banner-advanced>
        <div>Banner Content</div>
    </g-banner-advanced>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $canParse = AlexandriaExtractor::canParse($crawler);

    expect($canParse[0])->toBeTrue();
    expect($canParse[1])->toBe(1);
});

it('vue article extractor can parse returns correct priority', function () {
    $html = <<<'HTML'
<html>
<body>
    <div>No g-article here</div>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $canParse = VueArticleExtractor::canParse($crawler);

    expect($canParse[0])->toBeFalse();
    expect($canParse[1])->toBe(0);
});

it('default extractor can parse returns correct priority', function () {
    $html = <<<'HTML'
<html>
<body>
    <div class="segment">Default Segment Content</div>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $canParse = DefaultExtractor::canParse($crawler);

    expect($canParse[0])->toBeTrue();
    expect($canParse[1])->toBe(1);
});
