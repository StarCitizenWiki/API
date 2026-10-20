<?php

declare(strict_types=1);

namespace Tests\Unit\Parser\CommLink;

use App\Services\Parser\CommLink\Content;
use Symfony\Component\DomCrawler\Crawler;

function parsedText(string $content): string
{
    return trim((string) preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($content))));
}

it('extracts walker content from g-elements and preserves fallback text', function (): void {
    $content = (new Content(new Crawler(<<<'HTML'
        <div>
            <g-introduction :info='{"title":"Intro Title","subtitle":"Intro Subtitle","contents":["Body line 1","Body line 2"]}'></g-introduction>
            <g-banner-advanced :content='{"text":{"title":"Banner Title","subtitle":"Banner Subtitle","paragraph":"Banner paragraph"}}'></g-banner-advanced>
            <g-feature :is-header-declared="true">
                <template slot="title">Feature Title</template>
                <template slot="subtitle">Feature Subtitle</template>
            </g-feature>
            <g-unknown-element>Fallback text</g-unknown-element>
        </div>
        HTML)))->getContent();

    $text = parsedText($content);

    expect($text)->toContain('Intro Title')
        ->and($text)->toContain('Intro Subtitle')
        ->and($text)->toContain('Body line 1')
        ->and($text)->toContain('Banner Title')
        ->and($text)->toContain('Banner paragraph')
        ->and($text)->toContain('Feature Title')
        ->and($text)->toContain('Feature Subtitle')
        ->and($text)->toContain('Fallback text');
});

it('extracts alexandria component content in document order', function (): void {
    $content = (new Content(new Crawler(<<<'HTML'
        <div>
            <g-platform-client-component :properties='{"componentId":"Text","componentProps":{"text":"Body text","title":"Heading","description":"Subtitle"}}'></g-platform-client-component>
            <g-faq :question-list='[{"title":"First Question","content":"<p>First answer body</p>"},{"title":"Second Question","content":"<p>Second answer body</p>"}]'></g-faq>
        </div>
        HTML)))->getContent();

    $text = parsedText($content);

    expect($text)->toContain('Body text')
        ->and($text)->toContain('Heading')
        ->and($text)->toContain('First Question')
        ->and($text)->toContain('Second answer body');

    $firstQuestion = strpos($text, 'First Question');
    $secondAnswer = strpos($text, 'Second answer body');

    expect($firstQuestion)->not->toBeFalse()
        ->and($secondAnswer)->not->toBeFalse()
        ->and($firstQuestion)->toBeLessThan($secondAnswer);
});

it('drops navigation sales labels from the content', function (): void {
    $content = (new Content(new Crawler(<<<'HTML'
        <div>
            <g-platform-client-component :properties='{"componentId":"Text","componentProps":{"text":"Actual article body."}}'></g-platform-client-component>
            <g-navigation-sales :navigation='{"items":[{"label":"First"},{"label":"Second"}]}' :call-to-action='{"label":"FLY NOW","link":"https://robertsspaceindustries.com/fly"}'></g-navigation-sales>
        </div>
        HTML)))->getContent();

    $text = parsedText($content);

    expect($text)->toContain('Actual article body.')
        ->and($text)->not->toContain('FLY NOW')
        ->and($text)->not->toContain('First')
        ->and($text)->not->toContain('Second');
});

it('extracts default segment content', function (): void {
    $content = (new Content(new Crawler(<<<'HTML'
        <div>
            <div class="segment">
                <p>Segment body</p>
            </div>
        </div>
        HTML)))->getContent();

    expect(parsedText($content))->toContain('Segment body');
});

it('extracts layout-system residual content when no g-element exists', function (): void {
    $content = (new Content(new Crawler(<<<'HTML'
        <div id="layout-system">
            <div class="content">Layout body</div>
        </div>
        HTML)))->getContent();

    expect(parsedText($content))->toContain('Layout body');
});

it('returns an empty string for documents without content', function (): void {
    expect((new Content(new Crawler('<div><p>Unrelated</p></div>')))->getContent())->toBe('')
        ->and((new Content(new Crawler('')))->getContent())->toBe('');
});
