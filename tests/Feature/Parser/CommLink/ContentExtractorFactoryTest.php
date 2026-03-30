<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\ContentExtractorFactory;
use App\Services\Parser\CommLink\Content\DefaultExtractor;
use App\Services\Parser\CommLink\Content\LayoutSystemExtractor;
use App\Services\Parser\CommLink\Content\UniversalContentExtractor;
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
