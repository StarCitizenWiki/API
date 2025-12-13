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

    /**
     * Labels contain all available translations.
     *
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct()
    {
        $items = File::get(storage_path('app/api/scunpacked-data/labels.json'));
        $this->labels = collect(json_decode($items, true, 512, JSON_THROW_ON_ERROR));
        $this->loadChinese();
    }

    public function getData(): Collection
    {
        return $this->labels;
    }

    public function getDataZh(): Collection
    {
        return $this->zhTranslations;
    }

    private function loadChinese(): void
    {
        $this->zhTranslations = collect(parse_ini_file(
            storage_path('app/api/ScToolBoxLocales/chinese_(simplified)/global.ini'),
            scanner_mode: INI_SCANNER_RAW
        ));
    }
}
