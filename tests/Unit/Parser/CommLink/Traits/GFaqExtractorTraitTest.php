<?php

declare(strict_types=1);

namespace Tests\Unit\Parser\CommLink\Traits;

use App\Services\Parser\CommLink\Content\Traits\GFaqExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

function faqExtractor(): object
{
    return new class
    {
        use GFaqExtractorTrait;
    };
}

it('extracts numbered faq questions and preserves html content', function (): void {
    $result = faqExtractor()->getFaq(new Crawler(<<<'HTML'
        <g-faq :question-list='[{"title":"First Question","content":"<p>First answer</p>"},{"title":"Second Question","content":"<ul><li>Item 1</li></ul>"}]'></g-faq>
        HTML));

    expect($result)->toContain('<h3>1. First Question</h3>')
        ->and($result)->toContain('<p>First answer</p>')
        ->and($result)->toContain('<h3>2. Second Question</h3>')
        ->and($result)->toContain('<ul><li>Item 1</li></ul>');
});

it('skips incomplete faq questions', function (): void {
    $result = faqExtractor()->getFaq(new Crawler(<<<'HTML'
        <g-faq
          :question-list='[
            {"title":"Valid Question","content":"<p>Valid content</p>"},
            {"title":"Missing Content"},
            {"content":"Missing Title"}
          ]'>
        </g-faq>
        HTML));

    expect($result)->toContain('<h3>1. Valid Question</h3>')
        ->and($result)->toContain('<p>Valid content</p>')
        ->and($result)->not->toContain('<h3>2.')
        ->and($result)->not->toContain('<h3>3.');
});

it('returns an empty string for invalid or missing question lists', function (string $markup): void {
    expect(faqExtractor()->getFaq(new Crawler($markup)))->toBe('');
})->with([
    'invalid json' => [<<<'HTML'
        <g-faq :question-list='invalid json'></g-faq>
        HTML],
    'missing attribute' => [<<<'HTML'
        <g-faq></g-faq>
        HTML],
    'empty list' => [<<<'HTML'
        <g-faq :question-list='[]'></g-faq>
        HTML],
]);
