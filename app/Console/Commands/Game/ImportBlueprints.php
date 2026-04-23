<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
                ['uuid' => (string) $blueprintPayload['UUID']],
                ['uuid' => (string) $blueprintPayload['UUID']]
            );

            if ($blueprint->wasRecentlyCreated) {
                $newBlueprints++;
            } else {
                $existingBlueprints++;
            }

            $outputName = $this->normalizeString(Arr::get($blueprintPayload, 'Output.Name'));

            if ($blueprint->slug === null) {
                $blueprint->slug = $this->generateSlug(
                    $outputName ?? $this->normalizeString($blueprintPayload['Key']),
                    $blueprint->id,
                );
                $blueprint->save();
            }

            $blueprintData = BlueprintData::query()->updateOrCreate(
                [
                    'blueprint_id' => $blueprint->id,
                    'game_version_id' => $gameVersion->id,
                ],
                [
                    'key' => (string) $blueprintPayload['Key'],
                    'category_uuid' => (string) $blueprintPayload['CategoryUUID'],
                    'output_item_uuid' => (string) Arr::get($blueprintPayload, 'Output.UUID'),
                    'output_name' => $this->normalizeString(Arr::get($blueprintPayload, 'Output.Name')),
                    'output_class' => $this->normalizeString(Arr::get($blueprintPayload, 'Output.Class')),
                    'craft_time_seconds' => $this->extractCraftTimeSeconds($blueprintPayload),
                    'is_available_by_default' => (bool) Arr::get($blueprintPayload, 'Availability.Default', false),
                    'ingredient_resource_type_uuids' => $this->extractIngredientResourceTypeUuids($blueprintPayload),
                    'data' => $blueprintPayload,
                ]
            );

            $this->syncIngredients($blueprintData, $blueprintPayload);
            $this->syncDismantleReturns($blueprintData, $blueprintPayload);

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
        return $this->normalizeString($blueprintPayload['UUID'] ?? null) !== null
            && $this->normalizeString($blueprintPayload['Key'] ?? null) !== null
            && $this->normalizeString($blueprintPayload['CategoryUUID'] ?? null) !== null
            && $this->normalizeString(Arr::get($blueprintPayload, 'Output.UUID')) !== null;
    }

    private function extractCraftTimeSeconds(array $blueprintPayload): ?int
    {
        $craftTimeSeconds = Arr::get($blueprintPayload, 'Tiers.0.CraftTimeSeconds');

        return is_numeric($craftTimeSeconds) ? (int) $craftTimeSeconds : null;
    }

    /**
     * @return array<int, string>
     */
    private function extractIngredientResourceTypeUuids(array $blueprintPayload): array
    {
        $uuids = [];

        foreach ($blueprintPayload['Tiers'] ?? [] as $tier) {
            if (! is_array($tier)) {
                continue;
            }

            $this->collectIngredientResourceTypeUuids($tier['Requirements'] ?? null, $uuids);
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

        if (($node['Kind'] ?? null) === 'resource') {
            $uuid = $this->normalizeString($node['UUID'] ?? null);

            if ($uuid !== null) {
                $uuids[$uuid] = true;
            }

            return;
        }

        foreach ($node['Children'] ?? [] as $child) {
            $this->collectIngredientResourceTypeUuids($child, $uuids);
        }
    }

    private function syncIngredients(BlueprintData $blueprintData, array $blueprintPayload): void
    {
        $uuids = $this->extractIngredientResourceTypeUuids($blueprintPayload);

        $resourceTypes = Commodity::query()
            ->whereIn('uuid', $uuids)
            ->get()
            ->keyBy('uuid');

        $ids = [];

        foreach ($uuids as $uuid) {
            $resourceType = $resourceTypes->get($uuid);

            if ($resourceType === null) {
                $this->warn(sprintf('Skipping unknown ingredient resource type UUID: %s', $uuid));

                continue;
            }

            $ids[] = $resourceType->id;
        }

        $blueprintData->ingredients()->sync($ids);
    }

    private function syncDismantleReturns(BlueprintData $blueprintData, array $blueprintPayload): void
    {
        $returns = Arr::get($blueprintPayload, 'Dismantle.Returns', []);

        if (! is_array($returns) || $returns === []) {
            $blueprintData->dismantleReturns()->sync([]);

            return;
        }

        $returnUuids = collect($returns)
            ->filter(static fn (array $return): bool => ($return['Kind'] ?? null) === 'resource')
            ->pluck('UUID')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $resourceTypes = Commodity::query()
            ->whereIn('uuid', $returnUuids)
            ->get()
            ->keyBy('uuid');

        $syncData = [];

        foreach ($returns as $return) {
            if (($return['Kind'] ?? null) !== 'resource') {
                continue;
            }

            $uuid = $this->normalizeString($return['UUID'] ?? null);

            if ($uuid === null) {
                continue;
            }

            $resourceType = $resourceTypes->get($uuid);

            if ($resourceType === null) {
                $this->warn(sprintf('Skipping unknown dismantle return resource type UUID: %s', $uuid));

                continue;
            }

            $syncData[$resourceType->id] = [
                'quantity_scu' => (float) ($return['QuantityScu'] ?? 0),
            ];
        }

        $blueprintData->dismantleReturns()->sync($syncData);
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function generateSlug(?string $name, int $excludeId): string
    {
        $baseSlug = $name !== null ? Str::slug($name) : '';

        if ($baseSlug === '') {
            return 'blueprint-'.$excludeId;
        }

        $slug = $baseSlug;
        $counter = 2;

        while (Blueprint::query()->where('slug', $slug)->where('id', '!=', $excludeId)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
