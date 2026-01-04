<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\System\Language;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;
use Spatie\Translatable\HasTranslations;

final class TranslationResolver
{
    public static function resolve(
        mixed $source,
        Request $request,
        string $translationKey = 'translation'
    ): array|string|null|MissingValue {
        if ($source instanceof MissingValue) {
            return $source;
        }

        if ($source === null) {
            return new MissingValue;
        }

        $locale = $request->get('locale');

        if (is_string($locale) && $locale !== '') {
            return self::getSingleLocaleTranslation($source, $translationKey, substr($locale, 0, 2));
        }

        return self::getAllLocaleTranslations($source, $translationKey);
    }

    private static function getSingleLocaleTranslation(
        mixed $source,
        string $field,
        string $locale
    ): ?string {
        $value = $source->getTranslation($field, $locale, false);

        if (empty($value) && $locale !== Language::ENGLISH) {
            $value = $source->getTranslation($field, Language::ENGLISH, false);
        }

        return ! empty($value) ? $value : null;
    }

    /**
     * @param  HasTranslations  $source
     */
    private static function getAllLocaleTranslations(mixed $source, string $field): ?array
    {
        $translations = $source->getTranslations($field);

        if (empty($translations)) {
            return null;
        }

        $english = $translations[Language::ENGLISH] ?? null;
        $locales = Language::query()->pluck('code');

        if ($locales->isEmpty()) {
            return array_filter($translations, fn ($v) => ! empty($v)) ?: null;
        }

        $result = $locales->mapWithKeys(function (string $locale) use ($translations, $english): array {
            $value = $translations[$locale] ?? $english;

            return [$locale => $value];
        })
            ->filter(fn ($value) => ! empty($value))
            ->toArray();

        return ! empty($result) ? $result : null;
    }
}
