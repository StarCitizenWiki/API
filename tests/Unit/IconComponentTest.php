<?php

declare(strict_types=1);

use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

function renderedIcon(TestCase $testCase, string $template): Crawler
{
    $view = (fn (): mixed => $this->blade($template))->call($testCase);

    return new Crawler((string) $view);
}

it('renders an icon with the default contract attributes', function (): void {
    $icon = renderedIcon($this, '<x-icon name="search" />')->filter('i')->first();

    expect($icon->count())->toBe(1)
        ->and($icon->attr('data-lucide'))->toBe('search')
        ->and($icon->attr('class'))->toContain('size-4')
        ->and($icon->attr('class'))->toContain('shrink-0')
        ->and($icon->attr('aria-hidden'))->toBeNull();
});

it('preserves custom attributes and classes', function (): void {
    $icon = renderedIcon($this, '<x-icon name="search" class="text-error size-6" aria-hidden="true" />')->filter('i')->first();

    expect($icon->count())->toBe(1)
        ->and($icon->attr('data-lucide'))->toBe('search')
        ->and($icon->attr('aria-hidden'))->toBe('true')
        ->and($icon->attr('class'))->toContain('text-error')
        ->and($icon->attr('class'))->toContain('size-6')
        ->and($icon->attr('class'))->toContain('shrink-0');
});
