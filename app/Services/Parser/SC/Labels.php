<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

use App\Models\Game\GameLabel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class Labels
{
    /** @var array<string, Collection>|null */
    private static ?array $localeCaches = null;

    private static ?Collection $enLabelsLookup = null;

    public function getData(): Collection
    {
        if (self::$enLabelsLookup === null) {
            $this->loadFromDatabase();
        }

        return self::$enLabelsLookup;
    }

    public function getTranslation(string $localeCode, string $key): ?string
    {
        $normalized = ltrim($key, '@');

        return $this->getLocaleData($localeCode)?->get($normalized);
    }

    private function getLocaleData(string $localeCode): ?Collection
    {
        $locale = $this->normalizeLocale($localeCode);

        if (! in_array($locale, config('translations.locales'), true)) {
            return null;
        }

        if (self::$localeCaches === null) {
            $this->loadFromDatabase();
        }

        return self::$localeCaches[$locale] ?? null;
    }

    private function normalizeLocale(string $localeCode): string
    {
        // 'zh_CN' -> 'zh', 'de_DE' -> 'de', 'fr_FR' -> 'fr'
        return substr($localeCode, 0, 2);
    }

    private function loadFromDatabase(): void
    {
        self::$enLabelsLookup = collect(Cache::remember('labels:all', now()->addHours(24), function (): array {
            return GameLabel::all(['key', 'translation'])->mapWithKeys(function (GameLabel $label) {
                return [$label->key => $label->getTranslation('translation', 'en')];
            })->all();
        }));

        $locales = config('translations.locales', []);
        self::$localeCaches = [];

        foreach ($locales as $locale) {
            self::$localeCaches[$locale] = collect(Cache::remember("labels:{$locale}", now()->addHours(24), function () use ($locale): array {
                return GameLabel::all(['key', 'translation'])->mapWithKeys(function (GameLabel $label) use ($locale) {
                    return [$label->key => $label->getTranslation('translation', $locale, false)];
                })->filter()->all();
            }));
        }
    }
}
