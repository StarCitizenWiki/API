<?php

declare(strict_types=1);

use App\Exceptions\Translation\AuthenticationException;
use App\Exceptions\Translation\QuotaExceededException;
use App\Exceptions\Translation\RateLimitException;
use App\Exceptions\Translation\TranslationException;
use App\Services\Translation\TranslationService;
use DeepL\DeepLException;
use DeepL\TextResult;
use DeepL\Translator;

/**
 * @return array{sentenceCount: int, text: string}
 */
function makeChunkedTranslationFixture(): array
{
    $maxTextLength = (new ReflectionClass(TranslationService::class))->getConstant('MAX_TEXT_LENGTH');

    if (! is_int($maxTextLength)) {
        throw new RuntimeException('TranslationService::MAX_TEXT_LENGTH must be an integer.');
    }

    $sentences = [];
    $sentenceCount = 0;
    $textLength = 0;

    while ($textLength <= $maxTextLength) {
        $sentence = sprintf('Sentence %04d.', $sentenceCount + 1);
        $sentences[] = $sentence;
        $sentenceCount++;
        $textLength += strlen($sentence) + ($sentenceCount > 1 ? 1 : 0);
    }

    return [
        'sentenceCount' => $sentenceCount,
        'text' => implode(' ', $sentences),
    ];
}

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

it('applies german text replacements', function (): void {
    $mockTranslator = $this->mock(Translator::class);
    $mockResult = new TextResult('Die Geschenke der Sternenbürger sind toll.', 'de', 45);

    $mockTranslator->shouldReceive('translateText')
        ->once()
        ->andReturn($mockResult);

    $service = new TranslationService($mockTranslator);
    $result = $service->translate('Star Citizen gifts are great.', 'de');

    expect($result)->toBe('Die Star Citizen Geschenke sind toll.');
});

it('chunks long text while preserving sentence boundaries, order, and content', function (?string $formality, array $expectedOptions): void {
    ['sentenceCount' => $sentenceCount, 'text' => $longText] = makeChunkedTranslationFixture();
    $seenChunks = [];

    $mockTranslator = $this->mock(Translator::class);
    $mockTranslator->shouldReceive('translateText')
        ->andReturnUsing(function (
            string $chunk,
            string $sourceLocale,
            string $targetLocale,
            array $options,
        ) use (&$seenChunks, $expectedOptions): TextResult {
            expect($sourceLocale)->toBe('en');
            expect($targetLocale)->toBe('de');
            expect($options)->toBe($expectedOptions);

            $seenChunks[] = $chunk;

            return new TextResult($chunk, 'de', strlen($chunk));
        });

    $service = new TranslationService($mockTranslator);
    $result = $service->translate($longText, 'de', 'en', $formality);

    expect($seenChunks)->not->toBeEmpty()
        ->and(count($seenChunks))->toBeGreaterThan(1)
        ->and($result)->toBe(implode(' ', $seenChunks))
        ->and(preg_split('/\s+/', trim($result)))->toBe(preg_split('/\s+/', trim($longText)));

    $expectedSentence = 1;

    foreach ($seenChunks as $index => $chunk) {
        preg_match_all('/Sentence (\d{4})\./', $chunk, $matches);
        $chunkSentenceNumbers = array_map('intval', $matches[1]);

        expect($chunkSentenceNumbers)->not->toBeEmpty();
        expect($chunkSentenceNumbers)->toBe(range($expectedSentence, $expectedSentence + count($chunkSentenceNumbers) - 1));

        if ($index < count($seenChunks) - 1) {
            expect(substr($chunk, -1))->toBe('.');
        }

        $expectedSentence += count($chunkSentenceNumbers);
    }

    expect($expectedSentence - 1)->toBe($sentenceCount);
})->with([
    'without formality' => [null, []],
    'with formality' => ['less', ['formality' => 'less']],
]);

it('maps deepl exceptions', function (string $message, string $expectedException): void {
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
    'authentication exception' => ['Invalid auth key', AuthenticationException::class],
    'generic exception' => ['Some unknown error', TranslationException::class],
]);

it('replaces squadron 42 correctly', function (): void {
    $mockTranslator = $this->mock(Translator::class);
    $mockResult = new TextResult('Staffel 42 ist ein Spiel.', 'de', 26);

    $mockTranslator->shouldReceive('translateText')
        ->once()
        ->andReturn($mockResult);

    $service = new TranslationService($mockTranslator);
    $result = $service->translate('Squadron 42 is a game.', 'de');

    expect($result)->toBe('Squadron 42 ist ein Spiel.');
});

it('does not apply german replacements for other locales', function (): void {
    $mockTranslator = $this->mock(Translator::class);
    $mockResult = new TextResult('Some French text with Sternenbürger', 'fr', 36);

    $mockTranslator->shouldReceive('translateText')
        ->once()
        ->andReturn($mockResult);

    $service = new TranslationService($mockTranslator);
    $result = $service->translate('Some text', 'fr');

    expect($result)->toBe('Some French text with Sternenbürger');
});
