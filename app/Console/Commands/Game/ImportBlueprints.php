<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use JsonException;

use function Laravel\Prompts\select;

class ImportBlueprints extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:import-blueprints {version : Game version code to import} {--path=blueprints.json : Relative path to the blueprints JSON file on the scunpacked disk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import game blueprints for a specific game version';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $versionCode = (string) $this->argument('version');
        $path = (string) $this->option('path');

        $gameVersion = GameVersion::query()
            ->where('code', $versionCode)
            ->first();

        if ($gameVersion === null) {
            $this->error(sprintf('Game version "%s" does not exist. Please create it first.', $versionCode));

            return self::FAILURE;
        }

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
            $this->error(sprintf('%s must contain an array of blueprints.', $path));

            return self::FAILURE;
        }

        $imported = 0;
        $newBlueprints = 0;
        $existingBlueprints = 0;
        $skipped = 0;

        foreach ($payload as $blueprintPayload) {
            if (! is_array($blueprintPayload) || ! $this->isValidBlueprintPayload($blueprintPayload)) {
                $skipped++;

                continue;
            }

            $blueprint = Blueprint::query()->firstOrCreate(
                ['uuid' => (string) $blueprintPayload['uuid']],
                ['uuid' => (string) $blueprintPayload['uuid']]
            );

            if ($blueprint->wasRecentlyCreated) {
                $newBlueprints++;
            } else {
                $existingBlueprints++;
            }

            BlueprintData::query()->updateOrCreate(
                [
                    'blueprint_id' => $blueprint->id,
                    'game_version_id' => $gameVersion->id,
                ],
                [
                    'key' => (string) $blueprintPayload['key'],
                    'category_uuid' => (string) $blueprintPayload['category_uuid'],
                    'output_item_uuid' => (string) Arr::get($blueprintPayload, 'output.uuid'),
                    'output_name' => $this->extractOutputName($blueprintPayload),
                    'output_class' => $this->extractOutputClass($blueprintPayload),
                    'craft_time_seconds' => $this->extractCraftTimeSeconds($blueprintPayload),
                    'is_available_by_default' => (bool) Arr::get($blueprintPayload, 'availability.default', false),
                    'ingredient_resource_type_uuids' => $this->extractIngredientResourceTypeUuids($blueprintPayload),
                    'data' => $blueprintPayload,
                ]
            );

            $imported++;
        }

        $this->info(sprintf(
            'Imported %d blueprints for version %s (%d new identities, %d existing identities). Skipped %d invalid.',
            $imported,
            $gameVersion->code,
            $newBlueprints,
            $existingBlueprints,
            $skipped
        ));

        return self::SUCCESS;
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'version' => function (): string {
                $options = GameVersion::query()
                    ->orderByDesc('released_at')
                    ->orderBy('code')
                    ->pluck('code', 'code')
                    ->toArray();

                if ($options === []) {
                    $this->error('No game versions exist. Please create one before importing.');

                    return '';
                }

                return select(
                    label: 'Select game version to import',
                    options: $options
                );
            },
        ];
    }

    private function isValidBlueprintPayload(array $blueprintPayload): bool
    {
        return $this->normalizeString($blueprintPayload['uuid'] ?? null) !== null
            && $this->normalizeString($blueprintPayload['key'] ?? null) !== null
            && $this->normalizeString($blueprintPayload['category_uuid'] ?? null) !== null
            && $this->normalizeString(Arr::get($blueprintPayload, 'output.uuid')) !== null;
    }

    private function extractCraftTimeSeconds(array $blueprintPayload): ?int
    {
        $craftTimeSeconds = Arr::get($blueprintPayload, 'tiers.0.craft_time_seconds');

        return is_numeric($craftTimeSeconds) ? (int) $craftTimeSeconds : null;
    }

    private function extractOutputName(array $blueprintPayload): ?string
    {
        return $this->normalizeString(Arr::get($blueprintPayload, 'output.name'));
    }

    private function extractOutputClass(array $blueprintPayload): ?string
    {
        return $this->normalizeString(Arr::get($blueprintPayload, 'output.class'));
    }

    /**
     * @return array<int, string>
     */
    private function extractIngredientResourceTypeUuids(array $blueprintPayload): array
    {
        $uuids = [];

        foreach ($blueprintPayload['tiers'] ?? [] as $tier) {
            if (! is_array($tier)) {
                continue;
            }

            $this->collectIngredientResourceTypeUuids($tier['requirements'] ?? null, $uuids);
        }

        return array_values(array_keys($uuids));
    }

    /**
     * @param  array<string, bool>  $uuids
     */
    private function collectIngredientResourceTypeUuids(mixed $node, array &$uuids): void
    {
        if (! is_array($node)) {
            return;
        }

        if (($node['kind'] ?? null) === 'resource') {
            $uuid = $this->normalizeString($node['uuid'] ?? null);

            if ($uuid !== null) {
                $uuids[$uuid] = true;
            }

            return;
        }

        foreach ($node['children'] ?? [] as $child) {
            $this->collectIngredientResourceTypeUuids($child, $uuids);
        }
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
