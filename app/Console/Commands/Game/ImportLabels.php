<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Models\Game\GameLabel;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JsonException;

class ImportLabels extends Command
{
    protected $signature = 'game:import-labels';

    protected $description = 'Import translation labels from JSON and INI files into database';

    public function handle(): int
    {
        $labelsPath = config('translations.labels_json');

        if (! file_exists($labelsPath)) {
            $this->error("Labels file not found: {$labelsPath}");

            return self::FAILURE;
        }

        $this->info('Reading English labels...');

        try {
            $englishLabels = $this->readJsonLabels($labelsPath);
        } catch (FileNotFoundException|JsonException $e) {
            $this->error("Failed to read labels.json: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info("Loaded {$englishLabels->count()} English labels");

        $translations = $this->loadTranslations();

        $this->info('Creating/updating database records...');

        $bar = $this->output->createProgressBar($englishLabels->count());
        $bar->start();

        $labelsData = [];

        foreach ($englishLabels as $key => $value) {
            $labelTranslations = array_filter([
                'en' => $value,
                ...$translations->mapWithKeys(fn (Collection $trans, string $locale) => [
                    $locale => $trans->get($key),
                ])->toArray(),
            ]);

            $labelsData[] = [
                'id' => Str::uuid(),
                'key' => $key,
                'translation' => $labelTranslations,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($labelsData) >= 1000) {
                $this->upsertLabels($labelsData);
                $labelsData = [];
            }

            $bar->advance();
        }

        if (! empty($labelsData)) {
            $this->upsertLabels($labelsData);
        }

        $bar->finish();
        $this->newLine();

        $totalLabels = $englishLabels->count();
        $this->info("Successfully imported {$totalLabels} labels");

        return self::SUCCESS;
    }

    /**
     * Load all configured INI translation sources, keyed by short locale code.
     *
     * @return Collection<string, Collection<string, string>>
     */
    private function loadTranslations(): Collection
    {
        /** @var array<string, string> $sources */
        $sources = config('translations.sources', []);
        $locales = config('translations.locales', []);

        $localeMap = collect($sources)->mapWithKeys(fn (string $path, string $code) => [
            substr($code, 0, 2) => $path,
        ]);

        return collect($locales)
            ->mapWithKeys(function (string $locale) use ($localeMap) {
                $path = $localeMap->get($locale);

                if ($path === null) {
                    return [$locale => collect()];
                }

                $translations = $this->readIniTranslations($path);
                $this->info("Loaded {$translations->count()} ".strtoupper($locale).' translations');

                return [$locale => $translations];
            });
    }

    private function readJsonLabels(string $path): Collection
    {
        $content = File::get($path);

        return collect(json_decode($content, true, 512, JSON_THROW_ON_ERROR));
    }

    private function readIniTranslations(string $path): Collection
    {
        if (! file_exists($path)) {
            return collect();
        }

        return collect(parse_ini_file($path, scanner_mode: INI_SCANNER_RAW) ?: []);
    }

    private function upsertLabels(array $labelsData): void
    {
        $encodedData = array_map(function ($item) {
            $item['translation'] = json_encode($item['translation'], JSON_THROW_ON_ERROR);

            return $item;
        }, $labelsData);

        GameLabel::query()->upsert(
            $encodedData,
            ['key'],
            ['translation', 'updated_at']
        );
    }
}
