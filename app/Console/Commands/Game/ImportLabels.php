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
        $chinesePath = config('translations.sources.zh_CN');
        $germanPath = config('translations.sources.de_DE');

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

        $chineseTranslations = $this->readIniTranslations($chinesePath);
        $germanTranslations = $this->readIniTranslations($germanPath);

        $this->info("Loaded {$chineseTranslations->count()} Chinese translations");
        $this->info("Loaded {$germanTranslations->count()} German translations");

        $this->info('Creating/updating database records...');

        $bar = $this->output->createProgressBar($englishLabels->count());
        $bar->start();

        $labelsData = [];

        foreach ($englishLabels as $key => $value) {
            $labelsData[] = [
                'id' => Str::uuid(),
                'key' => $key,
                'translation' => array_filter([
                    'en' => $value,
                    'de' => $germanTranslations->get($key),
                    'zh' => $chineseTranslations->get($key),
                ]),
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
