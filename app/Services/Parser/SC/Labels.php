<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

use App\Models\Game\GameLabel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class Labels
{
    /** @var array<string, Collection> */
    private static array $localeCaches = [];

    public function getTranslation(string $localeCode, string $key): ?string
    {
        $normalized = ltrim($key, '@');

        return $this->getLocaleData($localeCode)?->get($normalized);
    }

    public static function flushCache(): void
    {
        self::$localeCaches = [];
    }

    private function getLocaleData(string $localeCode): ?Collection
    {
        $locale = $this->normalizeLocale($localeCode);

        if (! in_array($locale, config('translations.locales'), true)) {
            return null;
        }

        return self::$localeCaches[$locale] ??= collect(Cache::remember("labels:{$locale}", now()->addHours(24), function () use ($locale): array {
            return GameLabel::all(['key', 'translation'])->mapWithKeys(function (GameLabel $label) use ($locale) {
                return [$label->key => $label->getTranslation('translation', $locale, false)];
            })->filter()->all();
        }));
    }

    private function normalizeLocale(string $localeCode): string
    {
        // 'zh_CN' -> 'zh', 'de_DE' -> 'de', 'fr_FR' -> 'fr'
        return substr($localeCode, 0, 2);
    }
}
