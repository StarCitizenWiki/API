<?php

declare(strict_types=1);

namespace Tests\Feature\Parser\CommLink\Traits;

use App\Services\Parser\CommLink\Content\Traits\GIllustrationExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

it('extracts illustration with all attributes', function (): void {
    $html = '<g-illustration
        sign-link-href="https://robertsspaceindustries.com/community-hub/post/test"
        sign-name="Taavi_Spectre"
        sign-intro="By "
        image-size="fitContent"
        :simple-image="{\"test\":\"data\"}"></g-illustration>';

    $crawler = new Crawler($html);

    $trait = new class
    {
        use GIllustrationExtractorTrait;
    };

    $result = $trait->getIllustration($crawler);

    expect($result)->toBeString()
        ->toContain('<p class="illustration-credit">By </p>')
        ->toContain('<a href="https://robertsspaceindustries.com/community-hub/post/test">Taavi_Spectre</a>');
});

it('extracts illustration without link', function (): void {
    $html = '<g-illustration sign-name="ArtistName" sign-intro="Created by "></g-illustration>';

    $crawler = new Crawler($html);

    $trait = new class
    {
        use GIllustrationExtractorTrait;
    };

    $result = $trait->getIllustration($crawler);

    expect($result)->toBeString()
        ->toContain('<p class="illustration-credit">Created by </p>')
        ->toContain('ArtistName')
        ->not->toContain('<a');
});

it('returns empty string when no g-illustration found', function (): void {
    $html = '<div>No illustration here</div>';

    $crawler = new Crawler($html);

    $trait = new class
    {
        use GIllustrationExtractorTrait;
    };

    $result = $trait->getIllustration($crawler);

    expect($result)->toBeEmpty();
});

it('returns empty string when attributes are missing', function (): void {
    $html = '<g-illustration></g-illustration>';

    $crawler = new Crawler($html);

    $trait = new class
    {
        use GIllustrationExtractorTrait;
    };

    $result = $trait->getIllustration($crawler);

    expect($result)->toBeEmpty();
});

it('handles only sign-intro attribute', function (): void {
    $html = '<g-illustration sign-intro="Illustration: "></g-illustration>';

    $crawler = new Crawler($html);

    $trait = new class
    {
        use GIllustrationExtractorTrait;
    };

    $result = $trait->getIllustration($crawler);

    expect($result)->toBeString()
        ->toContain('<p class="illustration-credit">Illustration: </p>')
        ->not->toContain('<a');
});

it('handles only sign-name attribute without link', function (): void {
    $html = '<g-illustration sign-name="SoloArtist"></g-illustration>';

    $crawler = new Crawler($html);

    $trait = new class
    {
        use GIllustrationExtractorTrait;
    };

    $result = $trait->getIllustration($crawler);

    expect($result)->toBeString()
        ->toContain('SoloArtist')
        ->not->toContain('<p class="illustration-credit">');
});

it('gracefully ignores :simple-image json attribute', function (): void {
    $html = '<g-illustration
        sign-name="Artist"
        sign-intro="By "
        :simple-image="{\"originalFormat\":{\"desktop\":\"/i/test.png\"},\"alt\":\"Test Image\"}">
    </g-illustration>';

    $crawler = new Crawler($html);

    $trait = new class
    {
        use GIllustrationExtractorTrait;
    };

    $result = $trait->getIllustration($crawler);

    expect($result)->toBeString()
        ->toContain('<p class="illustration-credit">By </p>')
        ->toContain('Artist')
        ->not->toContain('simple-image')
        ->not->toContain('/i/test.png');
});

it('gracefully ignores image-size attribute', function (): void {
    $html = '<g-illustration
        sign-name="Artist"
        sign-intro="By "
        image-size="fitContent">
    </g-illustration>';

    $crawler = new Crawler($html);

    $trait = new class
    {
        use GIllustrationExtractorTrait;
    };

    $result = $trait->getIllustration($crawler);

    expect($result)->toBeString()
        ->toContain('<p class="illustration-credit">By </p>')
        ->toContain('Artist')
        ->not->toContain('fitContent');
});
