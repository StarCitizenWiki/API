<?php

declare(strict_types=1);

use Symfony\Component\DomCrawler\Crawler;

it('extracts title and content from g-header element', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-header :background-options="{&quot;isTransparent&quot;:false}">
  <template slot="title">IN-GAME REWARDS (FLAIR)</template>
  <template slot="content">
    <p>The 'verse is full of dangerous outlaws and questionable characters.</p>
  </template>
</g-header>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GHeaderExtractorTrait;
    };

    $result = $extractor->getHeader($crawler);

    expect($result)->toContain('<h1>IN-GAME REWARDS (FLAIR)</h1>');
    expect($result)->toContain('<p>The \'verse is full of dangerous outlaws and questionable characters.</p>');
});

it('preserves html in content slot', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-header :background-options="{}">
  <template slot="title">Test Title</template>
  <template slot="content">
    <p>Paragraph 1</p>
    <p><strong>Bold text</strong></p>
    <ul><li>Item 1</li><li>Item 2</li></ul>
  </template>
</g-header>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GHeaderExtractorTrait;
    };

    $result = $extractor->getHeader($crawler);

    expect($result)->toContain('<p>Paragraph 1</p>');
    expect($result)->toContain('<p><strong>Bold text</strong></p>');
    expect($result)->toContain('<ul><li>Item 1</li><li>Item 2</li></ul>');
});

it('handles missing title slot gracefully', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-header :background-options="{}">
  <template slot="content">
    <p>Content without title</p>
  </template>
</g-header>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GHeaderExtractorTrait;
    };

    $result = $extractor->getHeader($crawler);

    expect($result)->not->toContain('<h1>');
    expect($result)->toContain('<p>Content without title</p>');
});

it('handles missing content slot gracefully', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-header :background-options="{}">
  <template slot="title">Title Only</template>
</g-header>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GHeaderExtractorTrait;
    };

    $result = $extractor->getHeader($crawler);

    expect($result)->toContain('<h1>Title Only</h1>');
    expect($result)->not->toContain('<p>');
});

it('handles missing both slots gracefully', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<g-header :background-options="{}">
</g-header>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GHeaderExtractorTrait;
    };

    $result = $extractor->getHeader($crawler);

    expect($result)->toBe('');
});

it('handles missing g-header element', function () {
    $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
<p>No header here</p>
</body>
</html>
HTML;

    $crawler = new Crawler($html);

    $extractor = new class
    {
        use \App\Services\Parser\CommLink\Content\Traits\GHeaderExtractorTrait;
    };

    $result = $extractor->getHeader($crawler);

    expect($result)->toBe('');
});
