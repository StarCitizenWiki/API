<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\Traits\GAuthorExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

function authorExtractor(): object
{
    return new class
    {
        use GAuthorExtractorTrait;
    };
}

it('extracts author metadata when the author is complete', function (): void {
    $result = authorExtractor()->getAuthor(new Crawler(<<<'HTML'
        <g-author
          author-link="https://x.com/FreyjaV_"
          author-desc="Senior Community Manager"
          author-name="Freyja Vanadis"
          :simple-image='{"desktop":"/i/image.jpg"}'>
        </g-author>
        HTML));

    expect($result)->toContain('<h3>Freyja Vanadis</h3>')
        ->and($result)->toContain('<p>Senior Community Manager</p>')
        ->and($result)->toContain('<a href="https://x.com/FreyjaV_">Source</a>');
});

it('extracts author text without a source link', function (): void {
    $result = authorExtractor()->getAuthor(new Crawler(<<<'HTML'
        <g-author
          author-desc="Content Writer"
          author-name="John Doe"
          :simple-image='{}'>
        </g-author>
        HTML));

    expect($result)->toContain('<h3>John Doe</h3>')
        ->and($result)->toContain('<p>Content Writer</p>')
        ->and($result)->not->toContain('<a href=');
});

it('renders the author name when the description is missing', function (): void {
    $result = authorExtractor()->getAuthor(new Crawler(<<<'HTML'
        <g-author author-name="Jane Smith" :simple-image='{}'></g-author>
        HTML));

    expect($result)->toContain('<h3>Jane Smith</h3>')
        ->and($result)->not->toContain('<p>');
});

it('returns an empty string when the author is incomplete', function (string $markup): void {
    expect(authorExtractor()->getAuthor(new Crawler($markup)))->toBe('');
})->with([
    'missing author name' => [<<<'HTML'
        <g-author author-desc="Some description" :simple-image='{}'></g-author>
        HTML],
    'missing element' => [<<<'HTML'
        <p>No author element here</p>
        HTML],
]);
