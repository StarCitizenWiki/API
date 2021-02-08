<?php

namespace Tests\Feature\Services\Parser\CommLink\Content;

use App\Services\Parser\CommLink\Content\ContentExtractorFactory;
use App\Services\Parser\CommLink\Content\DefaultExtractor;
use App\Services\Parser\CommLink\Content\LayoutSystemExtractor;
use App\Services\Parser\CommLink\Content\VueArticleExtractor;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class ContentExtractorFactoryTest extends TestCase
{
    /**
     * @covers \App\Services\Parser\CommLink\Content\ContentExtractorFactory::getParserFromCrawler
     */
    public function testFactoryGetDefault(): void
    {
        $content = <<<Content
<html>
<head>
<title>Example</title>
</head>
<body>
<div class="segment">1</div>
<div class="segment">2</div>
<div class="segment">3</div>
<div class="segment">4</div>
<div id="layout-system"></div>
<g-article></g-article>
<g-article></g-article>
<g-article></g-article>
</body>
</html>
Content;

        $parser = ContentExtractorFactory::getParserFromCrawler(new Crawler($content));

        self::assertEquals(DefaultExtractor::class, get_class($parser));
    }

    /**
     * @covers \App\Services\Parser\CommLink\Content\ContentExtractorFactory::getParserFromCrawler
     */
    public function testFactoryGetLayoutSystem(): void
    {
        $content = <<<Content
<html>
<head>
<title>Example</title>
</head>
<body>
<div id="layout-system"></div>
</body>
</html>
Content;

        $parser = ContentExtractorFactory::getParserFromCrawler(new Crawler($content));

        self::assertEquals(LayoutSystemExtractor::class, get_class($parser));
    }

    /**
     * @covers \App\Services\Parser\CommLink\Content\ContentExtractorFactory::getParserFromCrawler
     */
    public function testFactoryGetVue(): void
    {
        $content = <<<Content
<html>
<head>
<title>Example</title>
</head>
<body>
<div class="segment">1</div>
<div class="segment">2</div>
<div class="segment">3</div>
<div id="layout-system"></div>
<g-article></g-article>
<g-article></g-article>
<g-article></g-article>
<g-article></g-article>
</body>
</html>
Content;

        $parser = ContentExtractorFactory::getParserFromCrawler(new Crawler($content));

        self::assertEquals(VueArticleExtractor::class, get_class($parser));
    }

    /**
     * @covers \App\Services\Parser\CommLink\Content\ContentExtractorFactory::getParserFromCrawler
     */
    public function testFactoryGetDefaultIfCountEqual(): void
    {
        $content = <<<Content
<html>
<head>
<title>Example</title>
</head>
<body>
<div class="segment">1</div>
<div class="segment">2</div>
<div class="segment">3</div>
<div id="layout-system"></div>
<g-article></g-article>
<g-article></g-article>
<g-article></g-article>
</body>
</html>
Content;

        $parser = ContentExtractorFactory::getParserFromCrawler(new Crawler($content));

        self::assertEquals(DefaultExtractor::class, get_class($parser));
    }
}
