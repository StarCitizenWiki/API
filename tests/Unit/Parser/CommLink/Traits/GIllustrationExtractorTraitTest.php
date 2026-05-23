<?php

declare(strict_types=1);

use App\Services\Parser\CommLink\Content\Traits\GIllustrationExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

function illustrationExtractor(): object
{
    return new class
    {
        use GIllustrationExtractorTrait;
    };
}

it('extracts linked illustration credits and ignores unrelated attributes', function (): void {
    $result = illustrationExtractor()->getIllustration(new Crawler(<<<'HTML'
        <g-illustration sign-intro="By " sign-name="Artist" sign-link-href="https://example.test/artist" image-size="fitContent" :simple-image="{&quot;desktop&quot;:&quot;/i/test.png&quot;}"></g-illustration>
        HTML));

    expect($result)->toContain('<p class="illustration-credit">By </p>')
        ->and($result)->toContain('<a href="https://example.test/artist">Artist</a>')
        ->and($result)->not->toContain('fitContent')
        ->and($result)->not->toContain('/i/test.png');
});

it('extracts illustration credits without a link', function (): void {
    $result = illustrationExtractor()->getIllustration(new Crawler(<<<'HTML'
        <g-illustration sign-intro="Created by " sign-name="SoloArtist"></g-illustration>
        HTML));

    expect($result)->toContain('<p class="illustration-credit">Created by </p>')
        ->and($result)->toContain('SoloArtist')
        ->and($result)->not->toContain('<a href=');
});

it('returns an empty string when illustration credits are missing', function (): void {
    expect(illustrationExtractor()->getIllustration(new Crawler('<g-illustration></g-illustration>')))->toBe('');
});
