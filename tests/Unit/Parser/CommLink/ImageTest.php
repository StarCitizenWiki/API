<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Image\Image as ImageModel;
use App\Services\Parser\CommLink\Image as ImageParser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;

uses(LazilyRefreshDatabase::class);

describe('og:image', function (): void {
    it('extracts the og:image meta tag as a cleaned image', function (): void {
        $html = <<<'HTML'
<html>
<head>
    <title>Og Image Page</title>
    <meta property="og:image" content="https://media.robertsspaceindustries.com/abcdef1234567890/banner.jpg">
</head>
<body>
<div id="post"><p>Hello world</p></div>
</body>
</html>
HTML;

        $imageIds = (new ImageParser(new Crawler($html)))->getImageIds();

        expect($imageIds)->toHaveCount(1);

        $image = ImageModel::query()->firstOrFail();

        expect($image->src)->toBe('/abcdef1234567890/source.jpg')
            ->and($image->alt)->toBe('og:image')
            ->and($image->dir)->toBe('abcdef1234567890');
    });

    it('does not extract anything when og:image is absent', function (): void {
        $html = <<<'HTML'
<html>
<head>
    <title>No Og Image Page</title>
</head>
<body>
<div id="post"><p>Hello world</p></div>
</body>
</html>
HTML;

        expect((new ImageParser(new Crawler($html)))->getImageIds())->toBe([])
            ->and(ImageModel::query()->count())->toBe(0);
    });
});
