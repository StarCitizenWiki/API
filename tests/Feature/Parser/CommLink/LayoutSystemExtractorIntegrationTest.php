<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\LayoutSystemExtractor;
use Symfony\Component\DomCrawler\Crawler;

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
    $content = $extractor->getContent();

    expect($content)
        ->toContain('<h1>Test Headline</h1>')
        ->toContain('<p>Test Byline<br /></p>');
});

it('extracts illustration content from html', function () {
    $html = <<<'HTML'
        <div id="layout-system">
            <g-illustration sign-intro="By " sign-name="TestArtist" />
        </div>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new LayoutSystemExtractor($crawler);
    $content = $extractor->getContent();

    expect($content)
        ->toContain('<p class="illustration-credit">By </p>')
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
    $content = $extractor->getContent();

    expect($content)
        ->toContain('<h3>John Doe</h3>')
        ->toContain('<p>Writer</p>');
});

it('extracts faq content from html', function () {
    $html = <<<'HTML'
        <div id="layout-system">
            <g-faq :question-list="[{&quot;title&quot;:&quot;Question 1&quot;,&quot;content&quot;:&quot;Answer 1&quot;}]" />
        </div>
        HTML;

    $crawler = new Crawler($html);
    $extractor = new LayoutSystemExtractor($crawler);
    $content = $extractor->getContent();

    expect($content)
        ->toContain('<h3>1. Question 1</h3>')
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
    $content = $extractor->getContent();

    expect($content)
        ->toContain('<h1>Test Title</h1>')
        ->toContain('<p>Test Content</p>');
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
    $content = $extractor->getContent();

    expect($content)
        ->toContain('<h1>Main Title</h1>')
        ->toContain('<h3>Jane Doe</h3>')
        ->toContain('<h1>Section 1</h1>')
        ->toContain('<p class="illustration-credit">Art by </p>')
        ->toContain('<h3>1. Q1</h3>');
});
