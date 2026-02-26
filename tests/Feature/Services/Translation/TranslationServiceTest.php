<?php

declare(strict_types=1);

use App\Exceptions\Translation\QuotaExceededException;
use App\Exceptions\Translation\RateLimitException;
use App\Exceptions\Translation\TranslationException;
use App\Services\Translation\TranslationService;
use DeepL\DeepLException;
use DeepL\TextResult;
use DeepL\Translator;

it(
    'translates text for locale permutations',
    function (
        string $text,
        string $targetLocale,
        string $expectedSourceLocale,
        ?string $sourceLocaleArgument,
        string $translatedText,
        string $detectedSourceLocale,
        int $billedCharacters,
    ): void {
        $mockTranslator = $this->mock(Translator::class);
        $mockResult = new TextResult($translatedText, $detectedSourceLocale, $billedCharacters);

        $mockTranslator->shouldReceive('translateText')
            ->once()
            ->with($text, $expectedSourceLocale, $targetLocale, [])
            ->andReturn($mockResult);

        $service = new TranslationService($mockTranslator);
        $result = $sourceLocaleArgument === null
            ? $service->translate($text, $targetLocale)
            : $service->translate($text, $targetLocale, $sourceLocaleArgument);

        expect($result)->toBe($translatedText);
    }
)->with([
    'simple text uses default source locale' => [
        'Hello World',
        'de',
        'en',
        null,
        'Hallo Welt',
        'de',
        11,
    ],
    'custom target locale keeps default source locale' => [
        'Hello world',
        'fr',
        'en',
        null,
        'Bonjour le monde',
        'fr',
        17,
    ],
    'custom source locale is forwarded' => [
        'Hallo Welt',
        'en',
        'de',
        'de',
        'Hello world',
        'en',
        11,
    ],
]);

it('applies German text replacements', function (): void {
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

it('chunks long text automatically', function (): void {
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

it('maps DeepL exceptions', function (string $message, string $expectedException): void {
    $mockTranslator = $this->mock(Translator::class);

    $mockTranslator->shouldReceive('translateText')
        ->once()
        ->andThrow(new DeepLException($message));

    $service = new TranslationService($mockTranslator);

    expect(fn () => $service->translate('Test', 'de'))
        ->toThrow($expectedException);
})->with([
    'rate limit exception' => ['Rate limit exceeded', RateLimitException::class],
    'quota exceeded exception' => ['Quota exceeded for this billing period', QuotaExceededException::class],
    'generic exception' => ['Some unknown error', TranslationException::class],
]);

it('replaces Squadron 42 correctly', function (): void {
    $mockTranslator = $this->mock(Translator::class);
    $mockResult = new TextResult('Staffel 42 ist ein Spiel.', 'de', 26);

    $mockTranslator->shouldReceive('translateText')
        ->once()
        ->andReturn($mockResult);

    $service = new TranslationService($mockTranslator);
    $result = $service->translate('Squadron 42 is a game.', 'de');

    expect($result)->toBe('Squadron 42 ist ein Spiel.');
});

it('does not apply German replacements for other locales', function (): void {
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
