<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Link as LinkModel;
use App\Services\Parser\CommLink\Link as LinkParser;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;

uses(LazilyRefreshDatabase::class);

describe('anchor links', function (): void {
    it('extracts external links with their text', function (): void {
        $html = <<<'HTML'
<div id="post">
    <p>Hello world</p>
    <a href="https://example.com/page">Example Page</a>
    <a href="/comm-link/relative">Relative Link</a>
</div>
HTML;

        $linkIds = (new LinkParser(new Crawler($html)))->getLinkIds();

        expect($linkIds)->toHaveCount(1);

        $link = LinkModel::query()->firstOrFail();

        expect($link->href)->toBe('https://example.com/page')
            ->and($link->text)->toBe('Example Page');
    });
});

describe('iframe links', function (): void {
    it('extracts an iframe with a https src', function (): void {
        $html = <<<'HTML'
<div id="post">
    <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"></iframe>
</div>
HTML;

        $linkIds = (new LinkParser(new Crawler($html)))->getLinkIds();

        expect($linkIds)->toHaveCount(1);

        $link = LinkModel::query()->firstOrFail();

        expect($link->href)->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ')
            ->and($link->text)->toBe('iframe');
    });

    it('prefixes protocol-relative iframe srcs with https', function (): void {
        $html = <<<'HTML'
<div id="post">
    <iframe src="//www.youtube.com/embed/dQw4w9WgXcQ"></iframe>
</div>
HTML;

        $linkIds = (new LinkParser(new Crawler($html)))->getLinkIds();

        expect($linkIds)->toHaveCount(1);

        $link = LinkModel::query()->firstOrFail();

        expect($link->href)->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ')
            ->and($link->text)->toBe('iframe');
    });

    it('prefers data-src over src', function (): void {
        $html = <<<'HTML'
<div id="post">
    <iframe src="about:blank" data-src="https://www.youtube.com/embed/dQw4w9WgXcQ" data-cookieconsent="marketing"></iframe>
</div>
HTML;

        $linkIds = (new LinkParser(new Crawler($html)))->getLinkIds();

        expect($linkIds)->toHaveCount(1);

        $link = LinkModel::query()->firstOrFail();

        expect($link->href)->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ')
            ->and($link->text)->toBe('iframe');
    });

    it('skips cookiebot consent iframes', function (): void {
        $html = <<<'HTML'
<div id="post">
    <iframe src="https://consent.cookiebot.com/abcdef/index.html"></iframe>
    <iframe src="about:blank" data-src="https://consent.cookiebot.com/abcdef"></iframe>
</div>
HTML;

        $linkIds = (new LinkParser(new Crawler($html)))->getLinkIds();

        expect($linkIds)->toBe([])
            ->and(LinkModel::query()->count())->toBe(0);
    });

    it('uses the iframe title as link text', function (): void {
        $html = <<<'HTML'
<div id="post">
    <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Star Citizen Trailer"></iframe>
</div>
HTML;

        $linkIds = (new LinkParser(new Crawler($html)))->getLinkIds();

        expect($linkIds)->toHaveCount(1);

        $link = LinkModel::query()->firstOrFail();

        expect($link->href)->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ')
            ->and($link->text)->toBe('Star Citizen Trailer');
    });
});

describe('video-id links', function (): void {
    it('extracts a youtube link from a video-id attribute', function (): void {
        $html = <<<'HTML'
<div id="layout-system">
    <g-trailer video-id="zNL_FgoTrjA" :picture="{}"></g-trailer>
</div>
HTML;

        $linkIds = (new LinkParser(new Crawler($html)))->getLinkIds();

        expect($linkIds)->toHaveCount(1);

        $link = LinkModel::query()->firstOrFail();

        expect($link->href)->toBe('https://www.youtube.com/watch?v=zNL_FgoTrjA')
            ->and($link->text)->toBe('video');
    });

    it('ignores an empty video-id attribute', function (): void {
        $html = <<<'HTML'
<div id="layout-system">
    <g-trailer video-id=""></g-trailer>
</div>
HTML;

        $linkIds = (new LinkParser(new Crawler($html)))->getLinkIds();

        expect($linkIds)->toBe([])
            ->and(LinkModel::query()->count())->toBe(0);
    });
});
