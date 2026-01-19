<?php

declare(strict_types=1);

namespace App\Services\Translation;

use App\Exceptions\Translation\AuthenticationException;
use App\Exceptions\Translation\QuotaExceededException;
use App\Exceptions\Translation\RateLimitException;
use App\Exceptions\Translation\TranslationException;
use DeepL\DeepLException;
use DeepL\Translator;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    /**
     * DeepL API limit is 50,000 bytes per request.
     * We use a slightly smaller limit to account for encoding differences.
     */
    private const MAX_TEXT_LENGTH = 45000;

    public function __construct(
        private readonly Translator $translator,
    ) {}

    /**
     * Translate text from source language to target language.
     *
     * @throws RateLimitException
     * @throws QuotaExceededException
     * @throws AuthenticationException
     * @throws TranslationException
     */
    public function translate(
        string $text,
        string $targetLocale,
        string $sourceLocale = 'en',
        ?string $formality = null,
    ): string {
        try {
            if (strlen($text) > self::MAX_TEXT_LENGTH) {
                $translation = $this->translateWithChunking($text, $targetLocale, $sourceLocale, $formality);
            } else {
                $options = $formality !== null ? ['formality' => $formality] : [];
                $result = $this->translator->translateText(
                    $text,
                    $sourceLocale,
                    $targetLocale,
                    $options,
                );
                $translation = $result->text;
            }

            $translation = $this->applyLanguageReplacements($translation, strtolower($targetLocale));

            return trim($translation);
        } catch (DeepLException $e) {
            Log::error('Translation failed', [
                'message' => $e->getMessage(),
                'text_length' => strlen($text),
                'source' => $sourceLocale,
                'target' => $targetLocale,
            ]);

            throw $this->mapException($e);
        }
    }

    /**
     * Translate long text by splitting into chunks.
     */
    private function translateWithChunking(
        string $text,
        string $targetLocale,
        string $sourceLocale,
        ?string $formality = null,
    ): string {
        $chunks = $this->chunkText($text);
        $translations = [];
        $options = $formality !== null ? ['formality' => $formality] : [];

        foreach ($chunks as $chunk) {
            $result = $this->translator->translateText(
                $chunk,
                $sourceLocale,
                $targetLocale,
                $options,
            );

            $translations[] = $result->text;
        }

        return implode(' ', $translations);
    }

    /**
     * Split text into chunks at sentence boundaries.
     *
     * @return string[]
     */
    private function chunkText(string $text): array
    {
        $chunks = [];
        $remaining = $text;

        while (strlen($remaining) > self::MAX_TEXT_LENGTH) {
            $chunk = substr($remaining, 0, self::MAX_TEXT_LENGTH);

            $lastPeriod = strrpos($chunk, '.');
            $lastNewline = strrpos($chunk, "\n");
            $breakPoint = max($lastPeriod, $lastNewline);

            if ($breakPoint !== false && $breakPoint > self::MAX_TEXT_LENGTH * 0.7) {
                $chunk = substr($chunk, 0, $breakPoint + 1);
            }

            $chunks[] = trim($chunk);
            $remaining = substr($remaining, strlen($chunk));
        }

        if ($remaining !== '') {
            $chunks[] = trim($remaining);
        }

        return $chunks;
    }

    /**
     * Apply language-specific text replacements to fix common translation issues.
     * Currently supports German (de) replacements for Star Citizen terminology.
     */
    private function applyLanguageReplacements(string $translation, string $locale): string
    {
        if ($locale !== 'de' && $locale !== 'de_de') {
            return $translation;
        }

        // German replacements for Star Citizen terminology
        $replacements = [
            'Geschenke der Sternenbürger' => 'Star Citizen Geschenke',
            'Überlieferungen der Sternenbürger' => 'Überlieferungen von Star Citizen',
            'im Sternenbürger TAG' => 'in Star Citizen TAG',
            'Woche im Sternenbürger' => 'Woche in Star Citizen',
            'Sternenbürger Live' => 'Star Citizen Live',
            'Sternenbürger-Community' => 'Star Citizen Community',
            'der Innere Sternenbürger' => 'Inside Star Citizen',
            'Im Inneren von Star Citizen' => 'Inside Star Citizen',
            'Gemeinschaft der Sternenbürger' => 'Star Citizen Community',
            'der Sternenbürger-Community' => 'der Star Citizen Community',
            'für alle Sternenbürger' => 'für alle Star Citizen',
            'Sternenbürger live' => 'Star Citizen live',
            'der Sternenbürger' => 'Star Citizen',
            'zur Sternenbürgerkunde' => 'zur Star Citizen Lore',
            'von Sternenbürger' => 'von Star Citizen',
            'den Sternenbürger' => 'Star Citizen',
            'im Sternenbürger' => 'Star Citizen',
            'des Sternenbürgers' => 'von Star Citizen',
            'Sternenbürger' => 'Star Citizen',
            'Sternenbürgern' => 'Star Citizen',
            'Grüße Bürgerinnen und Bürger' => '',
            'Grüße Bürger' => 'Grüße Citizens',
            'den Vers' => 'das Verse',
            'Staffel 42' => 'Squadron 42',
        ];

        foreach ($replacements as $from => $to) {
            $translation = str_replace($from, $to, $translation);
        }

        return $translation;
    }

    /**
     * Map DeepL exceptions to application-specific exceptions.
     */
    private function mapException(DeepLException $e): TranslationException
    {
        $message = $e->getMessage();

        if (str_contains(strtolower($message), 'rate limit') || str_contains(strtolower($message), 'too many requests')) {
            return new RateLimitException($message, previous: $e);
        }

        if (str_contains(strtolower($message), 'quota') || str_contains(strtolower($message), 'character limit')) {
            return new QuotaExceededException($message, previous: $e);
        }

        if (str_contains(strtolower($message), 'auth') || str_contains(strtolower($message), 'unauthorized') || str_contains(strtolower($message), 'invalid key')) {
            return new AuthenticationException($message, previous: $e);
        }

        return new TranslationException($message, previous: $e);
    }
}
