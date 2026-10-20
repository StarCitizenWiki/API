<?php

declare(strict_types=1);

namespace Tests\Unit\Parser\CommLink;

use App\Services\Parser\CommLink\JsonWalker;
use App\Services\Parser\CommLink\WalkerResult;
use Symfony\Component\DomCrawler\Crawler;

function collectWalker(string $html): WalkerResult
{
    $crawler = new Crawler;
    $crawler->addHtmlContent($html, 'UTF-8');

    return (new JsonWalker)->collect($crawler);
}

describe('dispositions', function (): void {
    it('skips layout-only dispositions subtrees entirely', function (): void {
        $result = collectWalker(<<<'HTML'
            <g-platform-client-component :properties='{"componentId":"MiniGrid","componentProps":{"gridOptions":{"uiData":{"elements":[{"key":"mg.text","dispositions":{"small":{"display":false,"position":{"column":0,"y":1.5},"dimension":{"columnSpan":4,"height":508}},"large":{"display":true}},"data":{"text":"Visible copy that survives the walk."}}]}}}}'></g-platform-client-component>
            HTML);

        expect($result->textBlocks)->toBe(['Visible copy that survives the walk.']);
    });

    it('descends only into the largest display breakpoint when present', function (): void {
        $result = collectWalker(<<<'HTML'
            <g-grid :cards='[{"content":{"title":"Card","dispositions":{"display":{"small":"Small only copy","large":"Large breakpoint copy survives here."}}}}]'></g-grid>
            HTML);

        expect($result->textBlocks)->toContain('Large breakpoint copy survives here.')
            ->and($result->textBlocks)->not->toContain('Small only copy');
    });
});

describe('dedup', function (): void {
    it('drops exact repeats of identical components', function (): void {
        $info = '{"title":"Same Title","contents":["Same body line"]}';

        $result = collectWalker(<<<HTML
            <div>
                <g-introduction :info='$info'></g-introduction>
                <g-introduction :info='$info'></g-introduction>
            </div>
            HTML);

        expect($result->textBlocks)->toBe(['Same Title', 'Same body line']);
    });

    it('normalizes whitespace before comparing blocks', function (): void {
        $result = collectWalker(<<<'HTML'
            <div>
                <g-faq title="Draft one"></g-faq>
                <g-faq title="Draft    one"></g-faq>
            </div>
            HTML);

        expect($result->textBlocks)->toBe(['Draft one']);
    });
});

describe('graceful degradation', function (): void {
    it('falls back to dom text for unknown components without attributes', function (): void {
        $result = collectWalker('<g-unknown-element><p>Unknown Content</p></g-unknown-element>');

        expect($result->textBlocks)->toBe(['Unknown Content']);
    });

    it('does not duplicate container text for nested g-* elements', function (): void {
        $result = collectWalker(<<<'HTML'
            <div>
                <g-narrative-group>
                    <g-article headline="Nested Headline" body="Nested article body with enough length."></g-article>
                </g-narrative-group>
            </div>
            HTML);

        expect(substr_count(implode("\n", $result->textBlocks), 'Nested Headline'))->toBe(1);
    });
});

describe('plain attributes', function (): void {
    it('treats g-article plain attributes as string leaves', function (): void {
        $result = collectWalker('<g-article headline="Article Headline" byline="By Someone" body="&lt;p&gt;One&lt;/p&gt;&lt;p&gt;Two&lt;/p&gt;"></g-article>');

        expect($result->textBlocks)->toBe(['Article Headline', 'By Someone', '<p>One</p><p>Two</p>']);
    });

    it('keeps short allowlisted keys and drops unknown short labels', function (): void {
        $result = collectWalker(<<<'HTML'
            <g-author author-name="Will Weissbaum" author-desc="Long enough description of the author here." layout="full-width"></g-author>
            HTML);

        expect($result->textBlocks)->toContain('Will Weissbaum')
            ->and($result->textBlocks)->not->toContain('full-width');
    });
});

describe('images', function (): void {
    it('emits the original source and webp max variants per picture object', function (): void {
        $result = collectWalker(<<<'HTML'
            <g-trailer :picture='{"originalFormat":{"source":"/i/aaa/source.jpg","max":"/i/aaa/max.jpg"},"webp":{"max":"/i/bbb/max.webp"},"alt":"ignored"}'></g-trailer>
            HTML);

        expect($result->images)->toBe(['/i/aaa/source.jpg', '/i/bbb/max.webp']);
    });

    it('prefers originalFormat.max over webp.max and keeps both formats', function (): void {
        $result = collectWalker(<<<'HTML'
            <g-banner-advanced :media='{"background":{"picture":{"originalFormat":{"max":"/i/orig/max.png"},"webp":{"max":"/i/webp/max.webp"}}}}'></g-banner-advanced>
            HTML);

        expect($result->images)->toBe(['/i/orig/max.png', '/i/webp/max.webp']);
    });

    it('falls back to any desktop variant', function (): void {
        $result = collectWalker(<<<'HTML'
            <g-illustration :simple-image='{"originalFormat":{"desktop":"/i/only/desktop.jpg"},"webp":{"desktop":"/i/only/desktop.webp"}}'></g-illustration>
            HTML);

        expect($result->images)->toBe(['/i/only/desktop.jpg', '/i/only/desktop.webp']);
    });

    it('collects absolute media urls from deep json', function (): void {
        $result = collectWalker(<<<'HTML'
            <g-platform-client-component :properties='{"componentProps":{"video":{"heapVideo":{"desktop":{"mp4":"https://media.robertsspaceindustries.com/0xramc66rebxw/mp4_1920.mp4"}}}}}'></g-platform-client-component>
            HTML);

        expect($result->images)->toBe(['https://media.robertsspaceindustries.com/0xramc66rebxw/mp4_1920.mp4']);
    });

    it('dedupes identical raw image strings', function (): void {
        $picture = '{"originalFormat":{"max":"/i/x/max.jpg"}}';

        $result = collectWalker(<<<HTML
            <div><g-feature :picture='$picture'></g-feature><g-feature :picture='$picture'></g-feature></div>
            HTML);

        expect($result->images)->toBe(['/i/x/max.jpg']);
    });
});

describe('html strings', function (): void {
    it('detects rich text even when it embeds media urls', function (): void {
        $result = collectWalker(<<<'HTML'
            <g-faq :question-list='[{"title":"Question?","content":"<ul><li><img src=\"/i/abc/photo.jpg\"></li><li>Answer</li></ul>"}]'></g-faq>
            HTML);

        expect($result->textBlocks)->toBe(['Question?', '<ul><li><img src="/i/abc/photo.jpg"></li><li>Answer</li></ul>'])
            ->and($result->images)->toBe([]);
    });

    it('does not classify single-tag strings as html', function (): void {
        $result = collectWalker('<g-styles theme="Title <b>with one marker</b> and length"></g-styles>');

        expect($result->textBlocks)->toBe([]);
    });
});

describe('noise filtering', function (): void {
    it('drops short non-allowlisted strings', function (): void {
        $result = collectWalker(<<<'HTML'
            <g-cta :call-to-action='{"label":"FLY NOW","link":""}'></g-cta>
            HTML);

        expect($result->textBlocks)->toBe([]);
    });

    it('drops numbers, hex colors, filenames and css values', function (): void {
        $result = collectWalker(<<<'HTML'
            <g-styles :theme='{"width":"1280","primary":"#ff0000","logo":"mfr-rsi-logo.png","spacing":"10px","other":"1.5rem"}'></g-styles>
            HTML);

        expect($result->textBlocks)->toBe([]);
    });

    it('keeps long sentences that merely end in words containing unit names', function (): void {
        $result = collectWalker('<g-quote :quote=\'{"text":"We built this system for them."}\'></g-quote>');

        expect($result->textBlocks)->toBe(['We built this system for them.']);
    });
});

describe('navigation sales', function (): void {
    it('excludes nav labels from text but captures the cta url', function (): void {
        $result = collectWalker(<<<'HTML'
            <g-navigation-sales :navigation='{"items":[{"label":"HOME","link":"#anchor"},{"label":"SHIPS","link":"#ships"}]}' :call-to-action='{"label":"FLY NOW","link":"https://robertsspaceindustries.com/fly-now"}'></g-navigation-sales>
            HTML);

        expect($result->textBlocks)->toBe([])
            ->and($result->urls)->toBe(['https://robertsspaceindustries.com/fly-now']);
    });

    it('still collects the nav logo picture', function (): void {
        $result = collectWalker(<<<'HTML'
            <g-navigation-sales :logo='{"simpleImage":{"originalFormat":{"max":"/i/logo/max.png"},"webp":{"max":"/i/logo/max.webp"}}}'></g-navigation-sales>
            HTML);

        expect($result->images)->toBe(['/i/logo/max.png', '/i/logo/max.webp']);
    });
});

describe('data uris', function (): void {
    it('discards data: uris', function (): void {
        $result = collectWalker(<<<'HTML'
            <g-banner :image='{"originalFormat":{"max":"data:image/png;base64,iVBORw0KGgo="}}'></g-banner>
            HTML);

        expect($result->images)->toBe([])
            ->and($result->textBlocks)->toBe([]);
    });
});
