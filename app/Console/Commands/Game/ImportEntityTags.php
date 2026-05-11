<?php

namespace App\Console\Commands\Game;

use App\Models\Game\EntityTag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use JsonException;

class ImportEntityTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:import-tags {--path=tags.json : Relative path to the tags JSON file on the scunpacked disk} {--disk=scunpacked : Storage disk to read from}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import game entity tags from scunpacked data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = (string) $this->option('path');

        $disk = Storage::disk($this->option('disk'));

        if ($disk->missing($path)) {
            $this->error(sprintf('%s not found in scunpacked storage.', $path));

            return self::FAILURE;
        }

        try {
            $contents = $disk->get($path);
            $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->error(sprintf('Failed to decode %s: %s', $path, $exception->getMessage()));

            return self::FAILURE;
        }

        if (! is_array($payload)) {
            $this->error(sprintf('%s must contain an object of tags.', $path));

            return self::FAILURE;
        }

        if ($payload === []) {
            $this->warn('No tags found in file.');

            return self::SUCCESS;
        }

        $now = now();
        $skipped = 0;

        $tags = collect($payload)
            ->filter(static function ($name, $uuid) use (&$skipped): bool {
                if (! is_string($uuid) || trim($uuid) === '') {
                    $skipped++;

                    return false;
                }

                if (! is_string($name) || trim($name) === '') {
                    $skipped++;

                    return false;
                }

                return true;
            })
            ->map(function (string $name, string $uuid) use ($now): array {
                return [
                    'uuid' => trim($uuid),
                    'name' => trim($name),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            });

        if ($tags->isEmpty()) {
            $this->warn('No valid tag records found to import.');

            return self::SUCCESS;
        }

        $totalCreated = 0;
        $totalUpdated = 0;

        foreach ($tags->chunk(10000) as $batch) {
            $batchUuids = $batch->pluck('uuid')->all();

            $existing = EntityTag::query()
                ->whereIn('uuid', $batchUuids)
                ->pluck('uuid')
                ->all();

            EntityTag::query()->upsert(
                $batch->values()->all(),
                ['uuid'],
                ['name', 'updated_at']
            );

            $batchCreated = count(array_diff($batchUuids, $existing));
            $totalCreated += $batchCreated;
            $totalUpdated += $batch->count() - $batchCreated;
        }

        $this->info(sprintf(
            'Imported %d entity tags (%d new, %d updated). Skipped %d invalid.',
            $tags->count(),
            $totalCreated,
            $totalUpdated,
            $skipped
        ));

        return self::SUCCESS;
    }
}
