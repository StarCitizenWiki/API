<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class MigrateTranslations extends Command
{
    protected $signature = 'data:migrate-translations
        {--from= : Source connection name (defaults to config data_migration.from_connection)}
        {--to= : Destination connection name (defaults to config data_migration.to_connection)}
        {--chunk=200 : Rows per batch update}';

    protected $description = 'Migrate translations from relational tables to JSON columns';

    public function handle(): int
    {
        $cfg = config('data_migration');
        $fromName = (string) ($this->option('from') ?: ($cfg['from_connection'] ?? 'legacy_mysql'));
        $toName = (string) ($this->option('to') ?: ($cfg['to_connection'] ?? 'pgsql'));
        $chunk = max(1, (int) $this->option('chunk'));

        $from = DB::connection($fromName);
        $to = DB::connection($toName);

        $this->info('Migrating translations to JSON columns...');

        $this->migrateSimple($from, $to, 'shipmatrix_production_statuses', 'production_status_translations', 'production_status_id', $chunk);
        $this->migrateSimple($from, $to, 'shipmatrix_production_notes', 'production_note_translations', 'production_note_id', $chunk);
        $this->migrateSimple($from, $to, 'shipmatrix_vehicle_sizes', 'vehicle_size_translations', 'size_id', $chunk);
        $this->migrateSimple($from, $to, 'shipmatrix_vehicle_types', 'vehicle_type_translations', 'type_id', $chunk);
        $this->migrateSimple($from, $to, 'shipmatrix_vehicle_foci', 'vehicle_focus_translations', 'focus_id', $chunk);
        $this->migrateSimple($from, $to, 'galactapedia_articles', 'galactapedia_article_translations', 'galactapedia_article_id', $chunk);
        $this->migrateSimple($from, $to, 'starmap_starsystems', 'starsystem_translations', 'starsystem_id', $chunk);
        $this->migrateSimple($from, $to, 'starmap_celestial_objects', 'celestial_object_translations', 'celestial_object_id', $chunk);
        $this->migrateSimple($from, $to, 'shipmatrix_vehicles', 'vehicle_translations', 'vehicle_id', $chunk);
        // $this->migrateByKey($from, $to, 'game_items', 'sc_item_translations', 'item_uuid', 'uuid', $chunk);
        $this->migrateSimple($from, $to, 'comm_links', 'comm_link_translations', 'comm_link_id', $chunk);

        $this->migrateManufacturer($from, $to, $chunk);

        $this->info('Translation migration complete.');

        return self::SUCCESS;
    }

    private function migrateSimple(
        ConnectionInterface $from,
        ConnectionInterface $to,
        string $parentTable,
        string $translationTable,
        string $foreignKey,
        int $chunk,
    ): void {
        $this->line("Migrating {$translationTable}...");

        $to->table($parentTable)->select('id')->orderBy('id')->chunkById(
            $chunk,
            function ($records) use ($from, $to, $translationTable, $foreignKey, $parentTable) {
                foreach ($records as $record) {
                    $translations = $from->table($translationTable)
                        ->where($foreignKey, $record->id)
                        ->get()
                        ->pluck('translation', 'locale_code')
                        ->filter(fn ($value) => $value !== null && $value !== '')
                        ->mapWithKeys(fn ($value, $key) => [substr($key, 0, 2) => $value])
                        ->toArray();

                    if ($translations === []) {
                        continue;
                    }

                    $to->table($parentTable)
                        ->where('id', $record->id)
                        ->update(['translation' => json_encode($translations)]);
                }
            }
        );
    }

    private function migrateManufacturer(ConnectionInterface $from, ConnectionInterface $to, int $chunk): void
    {
        $this->line('Migrating manufacturer_translations...');

        $to->table('shipmatrix_manufacturers')->select('id')->orderBy('id')->chunkById(
            $chunk,
            function ($records) use ($from, $to) {
                foreach ($records as $record) {
                    $translations = $from->table('manufacturer_translations')
                        ->where('manufacturer_id', $record->id)
                        ->get();

                    $knownFor = [];
                    $description = [];

                    foreach ($translations as $translation) {
                        if ($translation->known_for !== null && $translation->known_for !== '') {
                            $knownFor[substr($translation->locale_code, 0, 2)] = $translation->known_for;
                        }

                        if ($translation->description !== null && $translation->description !== '') {
                            $description[substr($translation->locale_code, 0, 2)] = $translation->description;
                        }
                    }

                    $to->table('shipmatrix_manufacturers')
                        ->where('id', $record->id)
                        ->update([
                            'known_for' => $knownFor !== [] ? json_encode($knownFor) : null,
                            'description' => $description !== [] ? json_encode($description) : null,
                        ]);
                }
            }
        );
    }

    private function migrateByKey(
        ConnectionInterface $from,
        ConnectionInterface $to,
        string $parentTable,
        string $translationTable,
        string $translationKey,
        string $parentKey,
        int $chunk,
    ): void {
        $this->line("Migrating {$translationTable}...");

        $to->table($parentTable)->select($parentKey)->orderBy($parentKey)->chunk(
            $chunk,
            function ($records) use ($from, $to, $translationTable, $translationKey, $parentTable, $parentKey) {
                foreach ($records as $record) {
                    $value = $record->{$parentKey};

                    $translations = $from->table($translationTable)
                        ->where($translationKey, $value)
                        ->get()
                        ->pluck('translation', 'locale_code')
                        ->filter(fn ($translation) => $translation !== null && $translation !== '')
                        ->mapWithKeys(fn ($translation, $locale) => [substr($locale, 0, 2) => $translation])
                        ->toArray();

                    if ($translations === []) {
                        continue;
                    }

                    $to->table($parentTable)
                        ->where($parentKey, $value)
                        ->update(['translation' => json_encode($translations)]);
                }
            }
        );
    }
}
