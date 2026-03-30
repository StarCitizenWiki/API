<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\ContentExtractorFactory;
use App\Services\Parser\CommLink\Content\DefaultExtractor;
use App\Services\Parser\CommLink\Content\LayoutSystemExtractor;
use App\Services\Parser\CommLink\Content\UniversalContentExtractor;
use Symfony\Component\DomCrawler\Crawler;

it('selects the extractor with the highest behavioural priority', function (
    string $html,
    ?string $expectedClass,
): void {
    $extractor = ContentExtractorFactory::getParserFromCrawler(new Crawler($html));

    if ($expectedClass === null) {
        expect($extractor)->toBeNull();

        return;
    }

    expect($extractor)->toBeInstanceOf($expectedClass);
})->with([
    'universal markup' => [
        '<g-introduction :info=\'{"title":"Intro"}\'></g-introduction>',
        UniversalContentExtractor::class,
    ],
    'universal g-article markup' => [
        '<g-article headline="Article Title" byline="Author" body="<p>Body</p>"></g-article>',
        UniversalContentExtractor::class,
    ],
    'universal alexandria markup' => [
        '<g-platform-client-component :properties=\'{"componentId":"Text","componentProps":{"title":"Alexandria Title"}}\'></g-platform-client-component>',
        UniversalContentExtractor::class,
    ],
    'universal g-feature markup' => [
        '<g-feature :is-header-declared="true"><template slot="title">Feature Title</template></g-feature>',
        UniversalContentExtractor::class,
    ],
    'universal outranks layout-system' => [
        '<g-introduction :info=\'{"title":"Intro"}\'></g-introduction><div id="layout-system">Layout</div>',
        UniversalContentExtractor::class,
    ],
    'layout-system markup' => [
        '<div id="layout-system"><div class="content">Layout</div></div>',
        LayoutSystemExtractor::class,
    ],
    'default segment markup' => [
        '<div class="segment">Default</div>',
        DefaultExtractor::class,
    ],
    'empty page' => [
        '<html><body></body></html>',
        null,
    ],
]);
