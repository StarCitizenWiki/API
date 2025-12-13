<?php

namespace App\Console\Commands\Game;

use App\Models\Game\Manufacturer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use JsonException;

class ImportManufacturers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:import-manufacturers {--path=manufacturers.json : Relative path to the manufacturers JSON file on the scunpacked disk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import game manufacturers from scunpacked data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = (string) $this->option('path');

        if (Storage::disk('scunpacked')->missing($path)) {
            $this->error(sprintf('%s not found in scunpacked storage.', $path));

            return self::FAILURE;
        }

        try {
            $contents = Storage::disk('scunpacked')->get($path);
            $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->error(sprintf('Failed to decode %s: %s', $path, $exception->getMessage()));

            return self::FAILURE;
        }

        if (! is_array($payload)) {
            $this->error(sprintf('%s must contain an array of manufacturers.', $path));

            return self::FAILURE;
        }

        $now = now();
        $skipped = 0;

        $manufacturers = collect($payload)
            ->filter(static function ($manufacturer) use (&$skipped): bool {
                if (! is_array($manufacturer)) {
                    $skipped++;

                    return false;
                }

                $hasRequiredKeys = isset($manufacturer['reference'], $manufacturer['name'], $manufacturer['code']);

                if (! $hasRequiredKeys) {
                    $skipped++;
                }

                return $hasRequiredKeys;
            })
            ->keyBy(fn (array $manufacturer): string => (string) $manufacturer['reference'])
            ->map(function (array $manufacturer) use ($now): array {
                return [
                    'uuid' => (string) $manufacturer['reference'],
                    'name' => (string) $manufacturer['name'],
                    'code' => (string) $manufacturer['code'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            });

        if ($manufacturers->isEmpty()) {
            $this->warn('No valid manufacturer records found to import.');

            return self::SUCCESS;
        }

        $uuids = $manufacturers->keys()->all();

        $existing = Manufacturer::query()
            ->whereIn('uuid', $uuids)
            ->pluck('uuid')
            ->all();

        Manufacturer::query()->upsert(
            $manufacturers->values()->all(),
            ['uuid'],
            ['name', 'code', 'updated_at']
        );

        $created = count(array_diff($uuids, $existing));
        $updated = $manufacturers->count() - $created;

        $this->info(sprintf(
            'Imported %d manufacturers (%d new, %d updated). Skipped %d invalid.',
            $manufacturers->count(),
            $created,
            $updated,
            $skipped
        ));

        return self::SUCCESS;
    }
}
