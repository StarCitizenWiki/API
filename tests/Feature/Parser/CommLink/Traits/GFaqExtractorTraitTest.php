<?php

declare(strict_types=1);

namespace Tests\Feature\Parser\CommLink\Traits;

use Symfony\Component\DomCrawler\Crawler;

it('extracts faq questions from g-faq element', function (): void {
    $html = '<g-faq :question-list=\'[{"title":"Test Question","content":"<p>Test Content</p>"}]\'></g-faq>';

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GFaqExtractorTrait;
    };

    $result = $extractor->getFaq($crawler);

    expect($result)->toContain('<h3>1. Test Question</h3>');
    expect($result)->toContain('<p>Test Content</p>');
});

it('handles invalid json gracefully', function (): void {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-faq :question-list='invalid json'></g-faq>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GFaqExtractorTrait;
    };

    $result = $extractor->getFaq($crawler);

    expect($result)->toBe('');
});

it('handles missing question-list attribute', function (): void {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-faq></g-faq>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GFaqExtractorTrait;
    };

    $result = $extractor->getFaq($crawler);

    expect($result)->toBe('');
});

it('handles empty questions array', function (): void {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-faq :question-list='[]'></g-faq>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GFaqExtractorTrait;
    };

    $result = $extractor->getFaq($crawler);

    expect($result)->toBe('');
});

it('handles missing title or content fields', function (): void {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-faq
  :question-list='[
    {
      "title": "Valid Question",
      "content": "<p>Valid content</p>"
    },
    {
      "title": "Missing Content"
    },
    {
      "content": "Missing Title"
    }
  ]'>
</g-faq>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GFaqExtractorTrait;
    };

    $result = $extractor->getFaq($crawler);

    expect($result)->toContain('<h3>1. Valid Question</h3>');
    expect($result)->toContain('<p>Valid content</p>');
    // Should not include missing content/title items
    expect($result)->not->toContain('<h3>2.');
    expect($result)->not->toContain('<h3>3.');
});

it('preserves html in content', function (): void {
    $html = '<g-faq :question-list=\'[{"title":"HTML Test","content":"<div><p>Paragraph</p><ul><li>Item 1</li><li>Item 2</li></ul></div>"}]\'></g-faq>';

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GFaqExtractorTrait;
    };

    $result = $extractor->getFaq($crawler);

    expect($result)->toContain('<div><p>Paragraph</p><ul><li>Item 1</li><li>Item 2</li></ul></div>');
});

it('handles multiple questions', function (): void {
    $html = '<g-faq :question-list=\'[{"title":"First Question","content":"<p>First answer</p>"},{"title":"Second Question","content":"<ul><li>Item 1</li></ul>"},{"title":"Third Question","content":"<p>Third answer</p>"}]\'></g-faq>';

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GFaqExtractorTrait;
    };

    $result = $extractor->getFaq($crawler);

    expect($result)->toContain('<h3>1. First Question</h3>');
    expect($result)->toContain('<p>First answer</p>');
    expect($result)->toContain('<h3>2. Second Question</h3>');
    expect($result)->toContain('<ul><li>Item 1</li></ul>');
    expect($result)->toContain('<h3>3. Third Question</h3>');
    expect($result)->toContain('<p>Third answer</p>');
});
