<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

use App\Models\Game\GameLabel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class Labels
{
    private static ?Collection $staticLabelsLookup = null;

    private static ?Collection $staticZhTranslations = null;

    private static ?Collection $staticDeTranslations = null;

    private ?Collection $labelsLookup = null;

    private ?Collection $zhTranslations = null;

    private ?Collection $deTranslations = null;

    public function getData(): Collection
    {
        if (self::$staticLabelsLookup === null) {
            $this->loadFromDatabase();
        }

        return self::$staticLabelsLookup;
    }

    public function getDataZh(): Collection
    {
        if (self::$staticZhTranslations === null) {
            $this->loadFromDatabase();
        }

        return self::$staticZhTranslations;
    }

    public function getDataDe(): Collection
    {
        if (self::$staticDeTranslations === null) {
            $this->loadFromDatabase();
        }

        return self::$staticDeTranslations;
    }

    public function getTranslation(string $localeCode, string $key): ?string
    {
        $normalized = ltrim($key, '@');

        return match ($localeCode) {
            'zh' => $this->getDataZh()->get($normalized),
            'zh_CN' => $this->getDataZh()->get($normalized),
            'de' => $this->getDataDe()->get($normalized),
            'de_DE' => $this->getDataDe()->get($normalized),
            default => null,
        };
    }

    private function loadFromDatabase(): void
    {
        self::$staticLabelsLookup = collect(Cache::remember('labels:all', now()->addHours(24), function (): array {
            return GameLabel::all(['key', 'translation'])->mapWithKeys(function (GameLabel $label) {
                return [$label->key => $label->getTranslation('translation', 'en')];
            })->all();
        }));

        self::$staticZhTranslations = collect(Cache::remember('labels:zh', now()->addHours(24), function (): array {
            $labels = GameLabel::all(['key', 'translation']);

            return $labels->mapWithKeys(function (GameLabel $label) {
                return [$label->key => $label->getTranslation('translation', 'zh')];
            })->filter()->all();
        }));

        self::$staticDeTranslations = collect(Cache::remember('labels:de', now()->addHours(24), function (): array {
            $labels = GameLabel::all(['key', 'translation']);

            return $labels->mapWithKeys(function (GameLabel $label) {
                return [$label->key => $label->getTranslation('translation', 'de')];
            })->filter()->all();
        }));
    }
}
