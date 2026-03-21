<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\Traits\GNarrativeGroupExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

it('extracts g-article headline', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-narrative-group :background-options='{"isTransparent":false}'>
  <section class="g-narrative-group_header">
    <g-article headline="Main Headline" />
  </section>
</g-narrative-group>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use GNarrativeGroupExtractorTrait;
    };

    $result = $extractor->getNarrativeGroup($crawler);

    expect($result)->toContain('<h1>Main Headline</h1>');
});

it('extracts g-article with headline, byline, and body', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-narrative-group :background-options='{"isTransparent":false}'>
  <section class="g-narrative-group_header">
    <g-article headline="Article Title" byline="Author Name" body="<p>Body content</p>" />
  </section>
</g-narrative-group>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use GNarrativeGroupExtractorTrait;
    };

    $result = $extractor->getNarrativeGroup($crawler);

    expect($result)->toContain('<h1>Article Title</h1>');
    expect($result)->toContain('<p>Author Name<br /></p>');
    expect($result)->toContain('<p>Body content</p>');
});

it('extracts g-illustration sign-intro and sign-name', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-narrative-group :background-options='{"isTransparent":false}'>
  <g-illustration sign-intro="By " sign-name="Artist Name" :simple-image='{"desktop":"/i/image.jpg"}' />
</g-narrative-group>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use GNarrativeGroupExtractorTrait;
    };

    $result = $extractor->getNarrativeGroup($crawler);

    expect($result)->toContain('<p class="illustration-credit">By </p>');
    expect($result)->toContain('Artist Name');
});

it('extracts g-illustration with link', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-narrative-group :background-options='{"isTransparent":false}'>
  <g-illustration
    sign-intro="Illustration by "
    sign-name="Artist"
    sign-link-href="https://example.com/link"
    :simple-image='{"desktop":"/i/image.jpg"}'
  />
</g-narrative-group>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use GNarrativeGroupExtractorTrait;
    };

    $result = $extractor->getNarrativeGroup($crawler);

    expect($result)->toContain('<a href="https://example.com/link">Artist</a>');
});

it('extracts multiple g-article and g-illustration elements', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-narrative-group :background-options='{"isTransparent":false}'>
  <section class="g-narrative-group_header">
    <g-article headline="First Article" byline="Author 1" body="<p>Content 1</p>" />
  </section>
  <g-illustration sign-intro="By " sign-name="Illustrator 1" :simple-image='{}' />
  <g-article headline="Second Article" body="<p>Content 2</p>" />
  <g-illustration sign-intro="Illustration by " sign-name="Illustrator 2" sign-link-href="https://link.com" :simple-image='{}' />
</g-narrative-group>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use GNarrativeGroupExtractorTrait;
    };

    $result = $extractor->getNarrativeGroup($crawler);

    expect($result)->toContain('<h1>First Article</h1>');
    expect($result)->toContain('<h1>Second Article</h1>');
    expect($result)->toContain('<p>Author 1<br /></p>');
    expect($result)->toContain('<p>Content 1</p>');
    expect($result)->toContain('<p>Content 2</p>');
    expect($result)->toContain('<p class="illustration-credit">By </p>');
    expect($result)->toContain('<p class="illustration-credit">Illustration by </p>');
    expect($result)->toContain('Illustrator 1');
    expect($result)->toContain('Illustrator 2');
    expect($result)->toContain('<a href="https://link.com">Illustrator 2</a>');
});

it('ignores :background-options attribute', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-narrative-group :background-options='{"isTransparent":false,"complex":"data"}'>
  <g-article headline="Test" :simple-image='{"desktop":"/i/image.jpg"}' />
</g-narrative-group>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use GNarrativeGroupExtractorTrait;
    };

    $result = $extractor->getNarrativeGroup($crawler);

    expect($result)->toContain('<h1>Test</h1>');
    expect($result)->not->toContain('isTransparent');
    expect($result)->not->toContain('desktop');
});

it('ignores :simple-image on g-illustration', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-narrative-group :background-options='{}'>
  <g-illustration sign-intro="By " sign-name="Artist" :simple-image='{"desktop":"/i/desktop.jpg","mobile":"/i/mobile.jpg"}' />
</g-narrative-group>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use GNarrativeGroupExtractorTrait;
    };

    $result = $extractor->getNarrativeGroup($crawler);

    expect($result)->toContain('Artist');
    expect($result)->not->toContain('desktop');
    expect($result)->not->toContain('mobile');
});

it('handles missing g-narrative-group element', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<p>No narrative group here</p>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use GNarrativeGroupExtractorTrait;
    };

    $result = $extractor->getNarrativeGroup($crawler);

    expect($result)->toBe('');
});

it('handles g-article without headline gracefully', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-narrative-group :background-options='{}'>
  <g-article byline="Author" body="<p>Body</p>" />
</g-narrative-group>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use GNarrativeGroupExtractorTrait;
    };

    $result = $extractor->getNarrativeGroup($crawler);

    expect($result)->not->toContain('<h1>');
    expect($result)->toContain('<p>Author<br /></p>');
    expect($result)->toContain('<p>Body</p>');
});

it('handles g-illustration without sign-name gracefully', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-narrative-group :background-options='{}'>
  <g-illustration sign-intro="By " :simple-image='{}' />
</g-narrative-group>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use GNarrativeGroupExtractorTrait;
    };

    $result = $extractor->getNarrativeGroup($crawler);

    expect($result)->toContain('<p class="illustration-credit">By </p>');
});

it('adds newlines between elements', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-narrative-group :background-options='{}'>
  <g-article headline="First" />
  <g-article headline="Second" />
</g-narrative-group>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use GNarrativeGroupExtractorTrait;
    };

    $result = $extractor->getNarrativeGroup($crawler);

    expect($result)->toContain("<h1>First</h1>\n<h1>Second</h1>");
});
