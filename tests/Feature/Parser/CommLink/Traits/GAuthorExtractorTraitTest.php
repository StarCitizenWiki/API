<?php

declare(strict_types=1);

use Symfony\Component\DomCrawler\Crawler;

test('extracts author information from g-author element', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-author
  author-link="https://x.com/FreyjaV_"
  author-desc="Senior Community Manager"
  author-name="Freyja Vanadis"
  :simple-image='{"desktop":"/i/image.jpg"}'>
</g-author>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GAuthorExtractorTrait;
    };

    $result = $extractor->getAuthor($crawler);

    expect($result)->toContain('<h3>Freyja Vanadis</h3>');
    expect($result)->toContain('<p>Senior Community Manager</p>');
    expect($result)->toContain('<a href="https://x.com/FreyjaV_">Source</a>');
});

test('extracts author without link', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-author
  author-desc="Content Writer"
  author-name="John Doe"
  :simple-image='{}'>
</g-author>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GAuthorExtractorTrait;
    };

    $result = $extractor->getAuthor($crawler);

    expect($result)->toContain('<h3>John Doe</h3>');
    expect($result)->toContain('<p>Content Writer</p>');
    expect($result)->not->toContain('<a href=');
});

test('handles missing author-name gracefully', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-author
  author-desc="Some description"
  :simple-image='{}'>
</g-author>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GAuthorExtractorTrait;
    };

    $result = $extractor->getAuthor($crawler);

    expect($result)->toBe('');
});

test('handles missing author-desc gracefully', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-author
  author-name="Jane Smith"
  :simple-image='{}'>
</g-author>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GAuthorExtractorTrait;
    };

    $result = $extractor->getAuthor($crawler);

    expect($result)->toContain('<h3>Jane Smith</h3>');
    expect($result)->not->toContain('<p>');
});

test('handles missing all attributes gracefully', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-author :simple-image='{}'></g-author>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GAuthorExtractorTrait;
    };

    $result = $extractor->getAuthor($crawler);

    expect($result)->toBe('');
});

test('handles missing g-author element', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<p>No author element here</p>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GAuthorExtractorTrait;
    };

    $result = $extractor->getAuthor($crawler);

    expect($result)->toBe('');
});

test('ignores :simple-image attribute', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-author
  author-name="Test Author"
  author-desc="Test Description"
  :simple-image='{"desktop":"/i/desktop.jpg","mobile":"/i/mobile.jpg","placeholder":{"desktop":"/i/placeholder.webp"}}'>
</g-author>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GAuthorExtractorTrait;
    };

    $result = $extractor->getAuthor($crawler);

    expect($result)->toContain('<h3>Test Author</h3>');
    expect($result)->toContain('<p>Test Description</p>');
    expect($result)->not->toContain('desktop');
    expect($result)->not->toContain('mobile');
    expect($result)->not->toContain('placeholder');
});

test('handles invalid :simple-image JSON gracefully', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-author
  author-name="Valid Author"
  author-desc="Valid Description"
  :simple-image='invalid json'>
</g-author>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GAuthorExtractorTrait;
    };

    $result = $extractor->getAuthor($crawler);

    expect($result)->toContain('<h3>Valid Author</h3>');
    expect($result)->toContain('<p>Valid Description</p>');
});
