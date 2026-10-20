<?php

declare(strict_types=1);

namespace Tests\Unit\Parser\CommLink;

use App\Services\Parser\CommLink\ContentExtractor;
use Symfony\Component\DomCrawler\Crawler;

function parsedExtractorText(string $content): string
{
    return trim((string) preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($content))));
}

describe('classic path', function (): void {
    it('extracts the introduction and segment markup', function (): void {
        $crawler = new Crawler(<<<'HTML'
            <html><body>
                <g-introduction :info='{"title":"Introduction Title","subtitle":"Subtitle","contents":["Intro line 1","Intro line 2"]}'></g-introduction>
                <div id="post">
                    <div class="segment"><p>Segment body one</p></div>
                    <div class="segment"><p>Segment body two</p></div>
                </div>
            </body></html>
            HTML);

        $content = (new ContentExtractor($crawler))->getContent();

        expect($content)->not->toBeNull();

        $text = parsedExtractorText((string) $content);

        expect($text)->toContain('Introduction Title')
            ->and($text)->toContain('Intro line 1')
            ->and($text)->toContain('Intro line 2');
    });

    it('extracts plain segments without any g-* element', function (): void {
        $crawler = new Crawler(<<<'HTML'
            <html><body><div id="post">
                <div class=" segment "><h2><center> Show Us Your Colors</h2><p>Contest rules body.</p></div>
            </div></body></html>
            HTML);

        $content = (new ContentExtractor($crawler))->getContent();

        expect($content)->not->toBeNull()
            ->and(parsedExtractorText((string) $content))->toContain('Show Us Your Colors')
            ->and(parsedExtractorText((string) $content))->toContain('Contest rules body.')
            ->and($content)->toContain('<center>');
    });

    it('falls back to the layout-system inner html', function (): void {
        $crawler = new Crawler(<<<'HTML'
            <html><body><div id="layout-system">
                <div class="content">Residual layout body</div>
            </div></body></html>
            HTML);

        $content = (new ContentExtractor($crawler))->getContent();

        expect(parsedExtractorText((string) $content))->toBe('Residual layout body');
    });

    it('appends the subscriber album wrapper', function (): void {
        $crawler = new Crawler(<<<'HTML'
            <html><body><div id="post"><div class="segment"><p>Post body</p></div></div>
            <div id="subscribers"><div class="album-wrapper"><p>Album body</p></div></div>
            </body></html>
            HTML);

        $content = (new ContentExtractor($crawler))->getContent();

        expect(parsedExtractorText((string) $content))->toContain('Post body')
            ->and(parsedExtractorText((string) $content))->toContain('Album body');
    });
});

describe('json walker path', function (): void {
    it('dispatches to the walker when g-* elements exist', function (): void {
        $crawler = new Crawler(<<<'HTML'
            <div>
                <g-banner-advanced :content='{"text":{"overline":"Overline","title":"Banner Title","subtitle":"Banner Subtitle","paragraph":"Banner paragraph text."}}'></g-banner-advanced>
                <g-article headline="Article Headline" byline="By Someone" body="&lt;p&gt;First&lt;/p&gt;&lt;p&gt;Second&lt;/p&gt;"></g-article>
                <g-unknown-element>Fallback text</g-unknown-element>
            </div>
            HTML);

        $content = (new ContentExtractor($crawler))->getContent();

        expect($content)->not->toBeNull();

        $text = parsedExtractorText((string) $content);

        expect($text)->toContain('Banner Title')
            ->and($text)->toContain('Banner paragraph text.')
            ->and($text)->toContain('Article Headline')
            ->and($text)->toContain('By Someone')
            ->and($text)->toContain('Fallback text');
    });

    it('joins walker blocks with a blank line', function (): void {
        $crawler = new Crawler(<<<'HTML'
            <g-introduction :info='{"title":"One","contents":["a"]}'></g-introduction>
            <g-introduction :info='{"title":"Two","contents":["b"]}'></g-introduction>
            HTML);

        $content = (new ContentExtractor($crawler))->getContent();

        expect($content)->toBeString()
            ->and($content)->toContain("\n\n");
    });

    it('returns null when nothing is found', function (): void {
        expect((new ContentExtractor(new Crawler('<div><p>Nothing relevant</p></div>')))->getContent())->toBeNull()
            ->and((new ContentExtractor(new Crawler('')))->getContent())->toBeNull();
    });
});
