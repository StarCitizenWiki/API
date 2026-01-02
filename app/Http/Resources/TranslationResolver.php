<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\System\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Collection;

final class TranslationResolver
{
    public static function resolve(mixed $source, Request $request, string $translationKey = 'translation'): array|string|null|MissingValue
    {
        if ($source instanceof MissingValue) {
            return $source;
        }

        if ($source === null) {
            return new MissingValue;
        }

        $translations = self::normalizeTranslations($source);

        if ($translations->isEmpty()) {
            return null;
        }

        $locale = $request->get('locale');

        if (is_string($locale) && $locale !== '') {
            return self::translationValue($translations->get($locale), $translationKey)
                ?? self::translationValue($translations->get(Language::ENGLISH), $translationKey);
        }

        return self::translationsForAllLocales($translations, $translationKey);
    }

    private static function normalizeTranslations(mixed $source): Collection
    {
        if ($source instanceof Collection) {
            return self::keyTranslationsByLocale($source);
        }

        if (is_object($source) && is_callable([$source, 'translations'])) {
            $translations = $source->translations;

            if ($translations instanceof Collection) {
                return self::keyTranslationsByLocale($translations);
            }
        }

        return collect();
    }

    private static function keyTranslationsByLocale(Collection $translations): Collection
    {
        return $translations
            ->filter(static fn ($translation) => data_get($translation, 'locale_code') !== null)
            ->keyBy(static fn ($translation) => data_get($translation, 'locale_code'));
    }

    private static function translationsForAllLocales(Collection $translations, string $translationKey): ?array
    {
        $locales = Language::query()->pluck('locale_code');

        if ($locales->isEmpty()) {
            return self::mapTranslations($translations, $translationKey);
        }

        $english = self::translationValue($translations->get(Language::ENGLISH), $translationKey);

        $filled = $locales->mapWithKeys(
            static function (string $locale) use ($translations, $translationKey, $english): array {
                $value = $translations->has($locale)
                    ? self::translationValue($translations->get($locale), $translationKey)
                    : $english;

                return [$locale => $value];
            }
        )->filter(static fn ($value) => ! empty($value));

        return $filled->isEmpty() ? null : $filled->toArray();
    }

    private static function mapTranslations(Collection $translations, string $translationKey): ?array
    {
        $values = $translations->mapWithKeys(
            static function ($translation) use ($translationKey): array {
                $locale = data_get($translation, 'locale_code');
                $value = self::translationValue($translation, $translationKey);

                return $locale !== null ? [$locale => $value] : [];
            }
        )->filter(static fn ($value) => ! empty($value));

        return $values->isEmpty() ? null : $values->toArray();
    }

    private static function translationValue(mixed $translation, string $translationKey): ?string
    {
        $value = data_get($translation, $translationKey);

        return is_string($value) ? $value : null;
    }
}
