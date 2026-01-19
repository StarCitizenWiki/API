<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class Labels
{
    private Collection $labels;

    private Collection $zhTranslations;

    private Collection $deTranslations;

    /**
     * Labels contain all available translations.
     *
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct(
        ?string $labelsPath = null,
        ?string $chinesePath = null,
        ?string $germanPath = null
    ) {
        $labelsPath ??= storage_path('app/api/scunpacked-data/labels.json');
        $chinesePath ??= storage_path('app/api/ScToolBoxLocales/chinese_(simplified)/global.ini');
        $germanPath ??= storage_path('app/api/StarCitizenDeutsch/live/full/global.ini');

        $items = File::get($labelsPath);
        $this->labels = collect(json_decode($items, true, 512, JSON_THROW_ON_ERROR));
        $this->loadChinese($chinesePath);
        $this->loadGerman($germanPath);
    }

    public function getData(): Collection
    {
        return $this->labels;
    }

    public function getDataZh(): Collection
    {
        return $this->zhTranslations;
    }

    public function getDataDe(): Collection
    {
        return $this->deTranslations;
    }

    /**
     * Get translation for a specific language.
     */
    public function getTranslation(string $localeCode, string $key): ?string
    {
        $normalized = ltrim($key, '@');

        return match ($localeCode) {
            'zh' => $this->zhTranslations->get($normalized),
            'zh_CN' => $this->zhTranslations->get($normalized),
            'de' => $this->deTranslations->get($normalized),
            'de_DE' => $this->deTranslations->get($normalized),
            default => null,
        };
    }

    private function loadChinese(string $path): void
    {
        if (! file_exists($path)) {
            $this->zhTranslations = collect();

            return;
        }

        $this->zhTranslations = collect(parse_ini_file(
            $path,
            scanner_mode: INI_SCANNER_RAW
        ) ?: []);
    }

    private function loadGerman(string $path): void
    {
        if (! file_exists($path)) {
            $this->deTranslations = collect();

            return;
        }

        $this->deTranslations = collect(parse_ini_file(
            $path,
            scanner_mode: INI_SCANNER_RAW
        ) ?: []);
    }
}
