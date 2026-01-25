<?php

declare(strict_types=1);

namespace Tests\Feature\Parser\CommLink;

use App\Services\Parser\CommLink\Content\AlexandriaExtractor;
use App\Services\Parser\CommLink\Content\ContentExtractorFactory;
use App\Services\Parser\CommLink\Content\DefaultExtractor;
use App\Services\Parser\CommLink\Content\LayoutSystemExtractor;
use App\Services\Parser\CommLink\Content\UniversalContentExtractor;
use App\Services\Parser\CommLink\Content\VueArticleExtractor;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

it('selects the correct extractor based on content', function (string $html, string $expectedClass) {
    $crawler = new Crawler($html);
    $extractor = ContentExtractorFactory::getParserFromCrawler($crawler);

    expect($extractor)->toBeInstanceOf($expectedClass);
})->with([
    'UniversalContentExtractor' => ['<g-introduction></g-introduction>', UniversalContentExtractor::class],
    'DefaultExtractor' => ['<div class="segment">Content</div>', DefaultExtractor::class],
    'LayoutSystemExtractor' => ['<div id="layout-system">Content</div>', LayoutSystemExtractor::class],
    // UniversalContentExtractor has higher priority (PHP_INT_MAX) for any g-* element
    'VueArticleExtractor (via Universal)' => ['<g-article headline="Test"></g-article>', UniversalContentExtractor::class],
    'AlexandriaExtractor (via Universal)' => ['<g-platform-client-component></g-platform-client-component>', UniversalContentExtractor::class],
]);

it('extracts content using UniversalContentExtractor for supported g-* elements', function (string $element, string $html, string $expectedContent) {
    $crawler = new Crawler($html);
    $extractor = new UniversalContentExtractor($crawler);

    expect($extractor->getContent())->toContain($expectedContent);
})->with([
    'g-introduction' => ['g-introduction', '<g-introduction :info=\'{"title": "Intro Text"}\'></g-introduction>', 'Intro Text'],
    'g-banner-advanced' => ['g-banner-advanced', '<g-banner-advanced :content=\'{"text": {"title": "Banner Title"}}\'></g-banner-advanced>', 'Banner Title'],
    'g-explore' => ['g-explore', '<g-explore :decks=\'[{"title": "Explore Title"}]\'></g-explore>', 'Explore Title'],
    'g-grid' => ['g-grid', '<g-grid :cards=\'[{"content": {"title": "Grid Title"}}]\'></g-grid>', 'Grid Title'],
    'g-skus' => ['g-skus', '<g-skus :properties=\'{"blocks": [{"type": "text", "properties": {"title": "Skus Title"}}]}\'></g-skus>', 'Skus Title'],
    'g-tumbril-features' => ['g-tumbril-features', '<g-tumbril-features :features="[{title: \'Tumbril Title\'}]"></g-tumbril-features>', 'Tumbril Title'],
    'g-narrative-group' => ['g-narrative-group', '<g-narrative-group><g-article headline="Narrative Title"></g-article></g-narrative-group>', 'Narrative Title'],
    'g-illustration' => ['g-illustration', '<g-illustration sign-intro="Illustration Title"></g-illustration>', 'Illustration Title'],
    'g-author' => ['g-author', '<g-author author-name="Author Name"></g-author>', 'Author Name'],
    'g-faq' => ['g-faq', '<g-faq :question-list=\'[{"title": "Q1", "content": "A1"}]\'></g-faq>', 'Q1'],
    'g-header' => ['g-header', '<g-header><template slot="title">Header Title</template></g-header>', 'Header Title'],
    'g-platform-client-component' => ['g-platform-client-component', '<g-platform-client-component :properties=\'{"componentId": "Text", "componentProps": {"title": "Alexandria Title"}}\'></g-platform-client-component>', 'Alexandria Title'],
]);

it('extracts g-feature using AlexandriaExtractor', function () {
    $html = '<g-feature><template slot="title">Feature Title</template></g-feature>';
    $crawler = new Crawler($html);
    $extractor = new AlexandriaExtractor($crawler);

    expect($extractor->getContent())->toContain('Feature Title');
});

it('extracts g-article using VueArticleExtractor', function () {
    $html = '<g-article headline="Article Headline"></g-article>';
    $crawler = new Crawler($html);
    $extractor = new VueArticleExtractor($crawler);

    expect($extractor->getContent())->toContain('Article Headline');
});

it('handles edge cases in UniversalContentExtractor', function () {
    // Empty content
    $crawler = new Crawler('');
    $extractor = new UniversalContentExtractor($crawler);
    expect($extractor->getContent())->toBe('');

    // Mixed content
    $crawler = new Crawler('<div>Standard HTML</div><g-introduction :info=\'{"title": "Intro"}\'></g-introduction>');
    $extractor = new UniversalContentExtractor($crawler);
    expect($extractor->getContent())->toContain('Intro'); // Universal only looks for g-* elements

    // Unknown g-* element
    Log::shouldReceive('warning')->once()->with('No extractor for <g-unknown>');
    $crawler = new Crawler('<g-unknown>Unknown Content</g-unknown>');
    $extractor = new UniversalContentExtractor($crawler);
    expect($extractor->getContent())->toBe('Unknown Content');
});

it('extracts content using DefaultExtractor', function () {
    $html = '<div class="segment">Segment 1</div><div class="segment">Segment 2</div>';
    $crawler = new Crawler($html);
    $extractor = new DefaultExtractor($crawler);

    expect($extractor->getContent())->toContain('Segment 1')->toContain('Segment 2');
});

it('extracts content using LayoutSystemExtractor', function () {
    $html = '<div id="layout-system"><g-introduction :info=\'{"title": "Intro"}\'></g-introduction><div class="content">Layout Content</div></div>';
    $crawler = new Crawler($html);
    $extractor = new LayoutSystemExtractor($crawler);

    expect($extractor->getContent())->toContain('Intro')->toContain('Layout Content');
});
