<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\UniversalContentExtractor;
use Symfony\Component\DomCrawler\Crawler;

it('getFilter returns asterisk for universal matching', function () {
    expect(UniversalContentExtractor::getFilter())->toBe('*');
});

it('canParse returns true and PHP_INT_MAX for HTML with g- elements', function () {
    $html = '<div><g-introduction :info="Hello">Test</g-introduction></div>';
    $crawler = new Crawler($html);

    [$canParse, $priority] = UniversalContentExtractor::canParse($crawler);

    expect($canParse)->toBeTrue();
    expect($priority)->toBe(PHP_INT_MAX);
});

it('canParse returns false and 0 for HTML without g- elements', function () {
    $html = '<div><p>Regular HTML content</p></div>';
    $crawler = new Crawler($html);

    [$canParse, $priority] = UniversalContentExtractor::canParse($crawler);

    expect($canParse)->toBeFalse();
    expect($priority)->toBe(0);
});

it('getContent extracts content from known g- elements', function () {
    $html = <<<'HTML'
        <div>
            <g-introduction :info="{&quot;title&quot;:&quot;Test Title&quot;,&quot;subtitle&quot;:&quot;Test Subtitle&quot;,&quot;contents&quot;:[&quot;Content line 1&quot;,&quot;Content line 2&quot;]}"></g-introduction>
            <g-banner-advanced :content="{&quot;displayed&quot;:true,&quot;text&quot;:{&quot;displayed&quot;:true,&quot;content&quot;:&quot;Banner text&quot;,&quot;paragraph&quot;:&quot;Banner paragraph&quot;}}"></g-banner-advanced>
        </div>
    HTML;

    $crawler = new Crawler($html);
    $extractor = new UniversalContentExtractor($crawler);

    $content = $extractor->getContent();

    expect($content)->toContain('Test Title');
    expect($content)->toContain('Test Subtitle');
    expect($content)->toContain('Content line 1');
    expect($content)->toContain('Banner paragraph');
});

it('getContent extracts multiple elements from same page', function () {
    $html = <<<'HTML'
        <div>
            <g-introduction :info="{&quot;title&quot;:&quot;First&quot;,&quot;contents&quot;:[&quot;Content 1&quot;]}"></g-introduction>
            <g-introduction :info="{&quot;title&quot;:&quot;Second&quot;,&quot;contents&quot;:[&quot;Content 2&quot;]}"></g-introduction>
            <g-banner-advanced :content="{&quot;text&quot;:{&quot;content&quot;:&quot;Third&quot;,&quot;paragraph&quot;:&quot;Banner paragraph&quot;}}"></g-banner-advanced>
        </div>
    HTML;

    $crawler = new Crawler($html);
    $extractor = new UniversalContentExtractor($crawler);

    $content = $extractor->getContent();

    expect($content)->toContain('First');
    expect($content)->toContain('Second');
    expect($content)->toContain('Banner paragraph');
});

it('getContent returns text content for unknown g- elements', function () {
    $html = '<div><g-unknown-element>Unknown text content</g-unknown-element></div>';
    $crawler = new Crawler($html);
    $extractor = new UniversalContentExtractor($crawler);

    $content = $extractor->getContent();

    expect($content)->toContain('Unknown text content');
});

it('getContent extracts content from real HTML sample', function () {
    $htmlPath = storage_path('app/comm_links/20913/2026-01-19_100707.html');

    if (! file_exists($htmlPath)) {
        $this->markTestSkipped('Required comm-link HTML fixture is missing for extraction coverage.');
    }

    $html = file_get_contents($htmlPath);
    $crawler = new Crawler($html);
    $extractor = new UniversalContentExtractor($crawler);

    $content = $extractor->getContent();

    expect($content)->toBeString();
    expect(strlen($content))->toBeGreaterThan(0);
});
