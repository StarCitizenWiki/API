<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\LayoutSystemExtractor;
use Symfony\Component\DomCrawler\Crawler;

function extractedText(string $content): string
{
    return trim((string) preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($content))));
}

it('extracts narrative-group content from html', function () {
    $html = <<<'HTML'
        <div id="layout-system">
            <g-narrative-group :background-options="{&quot;isTransparent&quot;:false}">
                <g-article headline="Test Headline" byline="Test Byline" />
            </g-narrative-group>
        </div>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new LayoutSystemExtractor($crawler);
    $text = extractedText($extractor->getContent());

    expect($text)
        ->toContain('Test Headline')
        ->toContain('Test Byline');
});

it('extracts illustration content from html', function () {
    $html = <<<'HTML'
        <div id="layout-system">
            <g-illustration sign-intro="By " sign-name="TestArtist" />
        </div>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new LayoutSystemExtractor($crawler);
    $text = extractedText($extractor->getContent());

    expect($text)
        ->toContain('By')
        ->toContain('TestArtist');
});

it('extracts author content from html', function () {
    $html = <<<'HTML'
        <div id="layout-system">
            <g-author author-name="John Doe" author-desc="Writer" />
        </div>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new LayoutSystemExtractor($crawler);
    $text = extractedText($extractor->getContent());

    expect($text)
        ->toContain('John Doe')
        ->toContain('Writer');
});

it('extracts faq content from html', function () {
    $html = <<<'HTML'
        <div id="layout-system">
            <g-faq :question-list="[{&quot;title&quot;:&quot;Question 1&quot;,&quot;content&quot;:&quot;Answer 1&quot;}]" />
        </div>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new LayoutSystemExtractor($crawler);
    $text = extractedText($extractor->getContent());

    expect($text)
        ->toContain('Question 1')
        ->toContain('Answer 1');
});

it('extracts header content from html', function () {
    $html = <<<'HTML'
        <div id="layout-system">
            <g-header :background-options="{&quot;isTransparent&quot;:false}">
                <template slot="title">Test Title</template>
                <template slot="content"><p>Test Content</p></template>
            </g-header>
        </div>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new LayoutSystemExtractor($crawler);
    $text = extractedText($extractor->getContent());

    expect($text)
        ->toContain('Test Title')
        ->toContain('Test Content');
});

it('extracts all 5 new traits from complex html', function () {
    $html = <<<'HTML'
        <div id="layout-system">
            <g-header :background-options="{&quot;isTransparent&quot;:false}">
                <template slot="title">Main Title</template>
            </g-header>
            <g-author author-name="Jane Doe" />
            <g-narrative-group>
                <g-article headline="Section 1" byline="By Jane Doe" />
            </g-narrative-group>
            <g-illustration sign-intro="Art by " sign-name="Artist Name" />
            <g-faq :question-list="[{&quot;title&quot;:&quot;Q1&quot;,&quot;content&quot;:&quot;A1&quot;}]" />
        </div>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new LayoutSystemExtractor($crawler);
    $text = extractedText($extractor->getContent());

    expect($text)
        ->toContain('Main Title')
        ->toContain('Jane Doe')
        ->toContain('Section 1')
        ->toContain('Art by')
        ->toContain('Q1');
});

it('does not leak extracted g-elements or duplicate extracted content from layout markup', function () {
    $html = <<<'HTML'
        <div id="layout-system">
            <g-header>
                <template slot="title">No Dupes</template>
                <template slot="content"><p>Scoped Body</p></template>
            </g-header>
            <div class="content">Layout Content</div>
        </div>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new LayoutSystemExtractor($crawler);
    $content = $extractor->getContent();
    $text = extractedText($content);

    expect(substr_count($text, 'No Dupes'))->toBe(1)
        ->and(substr_count($text, 'Scoped Body'))->toBe(1)
        ->and($text)->toContain('Layout Content')
        ->and($content)->not->toContain('<g-header')
        ->and($content)->not->toContain('id="layout-system"');
});
