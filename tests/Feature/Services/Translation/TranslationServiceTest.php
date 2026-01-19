<?php

declare(strict_types=1);

use App\Exceptions\Translation\QuotaExceededException;
use App\Exceptions\Translation\RateLimitException;
use App\Exceptions\Translation\TranslationException;
use App\Services\Translation\TranslationService;
use DeepL\DeepLException;
use DeepL\TextResult;
use DeepL\Translator;

it('translates simple text successfully', function () {
    $mockTranslator = $this->mock(Translator::class);
    $mockResult = new TextResult('Hallo Welt', 'de', 11);

    $mockTranslator->shouldReceive('translateText')
        ->once()
        ->with('Hello World', 'en', 'de', [])
        ->andReturn($mockResult);

    $service = new TranslationService($mockTranslator);
    $result = $service->translate('Hello World', 'de');

    expect($result)->toBe('Hallo Welt');
});

it('applies German text replacements', function () {
    $mockTranslator = $this->mock(Translator::class);
    $mockResult = new TextResult('Die Geschenke der Sternenbürger sind toll.', 'de', 45);

    $mockTranslator->shouldReceive('translateText')
        ->once()
        ->andReturn($mockResult);

    $service = new TranslationService($mockTranslator);
    $result = $service->translate('Star Citizen gifts are great.', 'de');

    expect($result)->toContain('Star Citizen Geschenke');
    expect($result)->not->toContain('Sternenbürger');
});

it('translates to custom target locale', function () {
    $mockTranslator = $this->mock(Translator::class);
    $mockResult = new TextResult('Bonjour le monde', 'fr', 17);

    $mockTranslator->shouldReceive('translateText')
        ->once()
        ->with('Hello world', 'en', 'fr', [])
        ->andReturn($mockResult);

    $service = new TranslationService($mockTranslator);
    $result = $service->translate('Hello world', 'fr');

    expect($result)->toBe('Bonjour le monde');
});

it('uses custom source locale when provided', function () {
    $mockTranslator = $this->mock(Translator::class);
    $mockResult = new TextResult('Hello world', 'en', 11);

    $mockTranslator->shouldReceive('translateText')
        ->once()
        ->with('Hallo Welt', 'de', 'en', [])
        ->andReturn($mockResult);

    $service = new TranslationService($mockTranslator);
    $result = $service->translate('Hallo Welt', 'en', 'de');

    expect($result)->toBe('Hello world');
});

it('chunks long text automatically', function () {
    // Create a text longer than 45,000 bytes
    $longText = str_repeat('This is a test sentence. ', 2000); // ~50,000 bytes

    $mockTranslator = $this->mock(Translator::class);
    $mockResult = new TextResult('Dies ist ein Testsatz.', 'de', 23);

    // Should receive multiple translateText calls (chunked)
    $mockTranslator->shouldReceive('translateText')
        ->atLeast()
        ->once()
        ->andReturn($mockResult);

    $service = new TranslationService($mockTranslator);
    $result = $service->translate($longText, 'de');

    expect($result)->toContain('Dies ist ein Testsatz');
});

it('maps rate limit exception', function () {
    $mockTranslator = $this->mock(Translator::class);

    $mockTranslator->shouldReceive('translateText')
        ->once()
        ->andThrow(new DeepLException('Rate limit exceeded'));

    $service = new TranslationService($mockTranslator);

    expect(fn () => $service->translate('Test', 'de'))
        ->toThrow(RateLimitException::class);
});

it('maps quota exceeded exception', function () {
    $mockTranslator = $this->mock(Translator::class);

    $mockTranslator->shouldReceive('translateText')
        ->once()
        ->andThrow(new DeepLException('Quota exceeded for this billing period'));

    $service = new TranslationService($mockTranslator);

    expect(fn () => $service->translate('Test', 'de'))
        ->toThrow(QuotaExceededException::class);
});

it('maps generic DeepL exception to TranslationException', function () {
    $mockTranslator = $this->mock(Translator::class);

    $mockTranslator->shouldReceive('translateText')
        ->once()
        ->andThrow(new DeepLException('Some unknown error'));

    $service = new TranslationService($mockTranslator);

    expect(fn () => $service->translate('Test', 'de'))
        ->toThrow(TranslationException::class);
});

it('replaces Squadron 42 correctly', function () {
    $mockTranslator = $this->mock(Translator::class);
    $mockResult = new TextResult('Staffel 42 ist ein Spiel.', 'de', 26);

    $mockTranslator->shouldReceive('translateText')
        ->once()
        ->andReturn($mockResult);

    $service = new TranslationService($mockTranslator);
    $result = $service->translate('Squadron 42 is a game.', 'de');

    expect($result)->toBe('Squadron 42 ist ein Spiel.');
});

it('does not apply German replacements for other locales', function () {
    $mockTranslator = $this->mock(Translator::class);
    $mockResult = new TextResult('Some French text with Sternenbürger', 'fr', 36);

    $mockTranslator->shouldReceive('translateText')
        ->once()
        ->andReturn($mockResult);

    $service = new TranslationService($mockTranslator);
    $result = $service->translate('Some text', 'fr');

    // Should NOT apply German replacements for French
    expect($result)->toContain('Sternenbürger');
});
