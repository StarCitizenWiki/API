<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\EntityTag;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\ItemDescriptionData;
use App\Models\Game\Manufacturer;
use App\Models\System\Language;
use App\Services\Game\SlugService;
use App\Services\Parser\SC\Labels;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;

class ImportItemData implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private static ?Labels $labels = null;

    private static ?Collection $entityTagsLookup = null;

    private static ?Collection $manufacturerLookup = null;

    private static array $itemCache = [];

    private static ?array $commodityUuidCache = null;

    public function __construct(
        private readonly int $gameVersionId,
        private readonly string $path,
        ?Labels $labels = null
    ) {
        if ($labels !== null) {
            self::$labels = $labels;
        }
    }

    /**
     * Execute the job.
     *
     * @throws JsonException
     */
    public function handle(): void
    {
        $payload = $this->readPayload();

        $itemPayload = $payload['Item'] ?? null;

        if (! is_array($itemPayload)) {
            return;
        }
        $uuid = $this->extractUuid($itemPayload);

        if ($uuid === null) {
            return;
        }

        $item = self::$itemCache[$uuid] ??= Item::query()->firstOrCreate(
            ['uuid' => $uuid],
            ['uuid' => $uuid]
        );

        $manufacturerId = $this->resolveManufacturerId($itemPayload, $uuid);

        $name = $this->extractName($itemPayload);

        $itemData = ItemData::query()->updateOrCreate(
            [
                'item_id' => $item->id,
                'game_version_id' => $this->gameVersionId,
            ],
            $this->mapItemData($itemPayload, $manufacturerId, $name)
        );

        $this->updateSlug($item, $name);

        $raw = $payload['Raw'] ?? [];

        $this->syncDescriptionData($item, $itemPayload, $raw);
        $this->syncTranslations($item, $itemPayload, $raw);
        $this->syncEntityTags($itemData, $itemPayload);
        $this->syncCommodities($itemData, $itemPayload);
    }

    /**
     * @throws JsonException
     */
    private function readPayload(): array
    {
        $contents = Storage::disk('scunpacked')->get($this->path);

        return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    }

    private function extractUuid(array $itemPayload): ?string
    {
        $uuid = $itemPayload['reference'] ?? $itemPayload['uuid'] ?? null;

        if (! is_string($uuid)) {
            return null;
        }

        $uuid = trim($uuid);

        return $uuid === '' ? null : $uuid;
    }

    private function resolveManufacturerId(array $itemPayload, string $uuid): int
    {
        $manufacturerUuid = Arr::get($itemPayload, 'stdItem.Manufacturer.UUID', '00000000-0000-0000-0000-000000000000');

        if (self::$manufacturerLookup === null) {
            self::$manufacturerLookup = Manufacturer::query()
                ->get(['id', 'uuid'])
                ->keyBy('uuid');
        }

        $manufacturer = self::$manufacturerLookup->get($manufacturerUuid);

        if ($manufacturer === null) {
            // Lazy-reload: manufacturer may have been created after initial cache load (e.g. in tests)
            self::$manufacturerLookup = Manufacturer::query()
                ->get(['id', 'uuid'])
                ->keyBy('uuid');
            $manufacturer = self::$manufacturerLookup->get($manufacturerUuid);
        }

        if ($manufacturer === null) {
            throw new RuntimeException(sprintf('Manufacturer with uuid %s does not exist for item %s.', $manufacturerUuid, $uuid));
        }

        return $manufacturer->id;
    }

    private function mapItemData(array $itemPayload, int $manufacturerId, string $name): array
    {
        unset($itemPayload['name'], $itemPayload['itemName']);

        $itemClass = Arr::get($itemPayload, 'stdItem.DescriptionData.Class');

        if (! in_array($itemClass, ['Industrial', 'Civilian', 'Military', 'Stealth', 'Competition'], true)) {
            $itemClass = null;
        }

        return [
            'manufacturer_id' => $manufacturerId,
            'name' => $name,
            'class_name' => $itemPayload['className'] ?? null,
            'type' => $itemPayload['type'] ?? null,
            'sub_type' => $itemPayload['subType'] ?? null,
            'classification' => $itemPayload['classification'] ?? null,
            'size' => $this->nullableInt($itemPayload['size'] ?? null),
            'grade' => $this->nullableInt($itemPayload['grade'] ?? null),
            'class' => $itemClass,
            'base_id' => null,

            'data' => $itemPayload,
        ];
    }

    private function extractName(array $itemPayload): string
    {
        $candidates = [
            $itemPayload['name'] ?? null,
            $itemPayload['itemName'] ?? null,
            $itemPayload['className'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return $candidate;
            }
        }

        return 'Unknown Item';
    }

    private function syncDescriptionData(Item $item, array $itemPayload, array $raw): void
    {
        $descriptionData = Arr::get($itemPayload, 'stdItem.DescriptionData', []);

        if (! is_array($descriptionData) || $descriptionData === []) {
            return;
        }

        foreach ($descriptionData as $name => $value) {
            if (! is_string($name) || $name === '') {
                continue;
            }

            ItemDescriptionData::query()->updateOrCreate(
                [
                    'item_id' => $item->id,
                    'name' => $name,
                ],
                [
                    'value' => is_scalar($value) ? (string) $value : json_encode($value),
                ]
            );
        }
    }

    private function syncTranslations(Item $item, array $itemPayload, array $raw): void
    {
        $updated = $this->syncEnglishTranslation($item, $raw, $itemPayload);

        $descriptionLabel = $this->extractDescriptionLabel($raw);

        if ($descriptionLabel === null) {
            return;
        }

        foreach ([Language::CHINESE, Language::GERMAN] as $language) {
            try {
                $updated = $this->syncLanguageTranslation($item, $descriptionLabel, $language) || $updated;
            } catch (Exception $e) {
                Log::warning("Failed to sync {$language} translation", [
                    'item_id' => $item->id,
                    'label' => $descriptionLabel,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($updated) {
            $item->save();
        }
    }

    private function syncEnglishTranslation(Item $item, array $raw, array $itemPayload): bool
    {
        $english = $this->extractEnglishDescription($raw, $itemPayload);

        if ($english === null || $english === '') {
            return false;
        }

        $item->setTranslation('translation', Language::ENGLISH, $english);

        return true;
    }

    private function syncLanguageTranslation(Item $item, string $label, string $localeCode): bool
    {
        $translation = $this->getLabels()->getTranslation($localeCode, $label);

        if ($translation === null || $translation === '') {
            return false;
        }

        $item->setTranslation('translation', $localeCode, $this->getDescriptionText($translation));

        return true;
    }

    private function extractDescriptionLabel(array $raw): ?string
    {
        $component = Arr::get($raw, 'Entity.Components.SAttachableComponentParams.AttachDef', []);

        $label = $component['Localization__Description'] ?? Arr::get($component, 'Localization.__Description');

        if (! is_string($label)) {
            return null;
        }

        $label = trim($label);

        if ($label === '' || mb_strtolower($label) === '@loc_empty') {
            return null;
        }

        return ltrim($label, '@');
    }

    private function extractEnglishDescription(array $raw, array $itemPayload): ?string
    {
        $component = Arr::get($raw, 'Entity.Components.SAttachableComponentParams.AttachDef', []);
        $localization = $component['Localization'] ?? [];

        $candidates = [
            Arr::get($itemPayload, 'stdItem.DescriptionText'),
            Arr::get($itemPayload, 'stdItem.Description'),
            Arr::get($localization, 'English.Description'),
            Arr::get($localization, 'Description'),
        ];

        return array_find($candidates, fn ($candidate) => is_string($candidate) && trim($candidate) !== '');
    }

    private function getLabels(): Labels
    {
        if (self::$labels === null) {
            self::$labels = new Labels;
        }

        return self::$labels;
    }

    private function getEntityTagsLookup(): Collection
    {
        if (self::$entityTagsLookup === null) {
            self::$entityTagsLookup = EntityTag::query()
                ->get(['id', 'uuid', 'name'])
                ->keyBy('uuid');
        }

        return self::$entityTagsLookup;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    /**
     * Tries to remove the leading part of a description containing data
     */
    private function getDescriptionText(string $description): string
    {
        $description = str_replace('\\n \\n', '\\n\\n', $description);

        $description = trim(str_replace('\n', "\n", $description));
        $description = str_replace(['‘', '’', '`', '´', ' '], ['\'', '\'', '\'', '\'', ' '], $description);
        $exploded = explode("\n\n", $description);

        if (count($exploded) === 1) {
            $exploded = explode('\n\n', $exploded[0]);
        }

        $exploded = array_filter($exploded, static function (string $part) {
            return preg_match('/(：|\w:[\s| ])/u', $part) !== 1;
        });

        return trim(implode("\n\n", $exploded));
    }

    private function syncEntityTags(ItemData $itemData, array $itemPayload): void
    {
        $entityTagMap = $itemPayload['entity_tag_map'] ?? [];

        if (! is_array($entityTagMap) || $entityTagMap === []) {
            $itemData->entityTags()->sync([]);

            return;
        }

        $validTags = collect($entityTagMap)
            ->filter(function ($tagData) {
                return isset($tagData['tag'], $tagData['name']) && is_array($tagData) && is_string($tagData['tag']) && is_string($tagData['name']);
            });

        if ($validTags->isEmpty()) {
            $itemData->entityTags()->sync([]);

            return;
        }

        $lookup = $this->getEntityTagsLookup();

        $missingTags = $validTags->filter(function ($tagData) use ($lookup) {
            return ! $lookup->has($tagData['tag']);
        });

        if ($missingTags->isNotEmpty()) {
            $tagsToCreate = $missingTags->map(function ($tagData) {
                return [
                    'uuid' => $tagData['tag'],
                    'name' => $tagData['name'],
                ];
            })->values()->all();

            EntityTag::query()->upsert(
                $tagsToCreate,
                ['uuid'],
                ['name', 'updated_at']
            );

            self::$entityTagsLookup = null;
        }

        $tagIds = $validTags
            ->map(fn ($tagData) => $this->getEntityTagsLookup()->get($tagData['tag']))
            ->filter()
            ->pluck('id')
            ->all();

        $itemData->entityTags()->sync($tagIds);
    }

    private function syncCommodities(ItemData $itemData, array $itemPayload): void
    {
        $defaultComposition = Arr::get($itemPayload, 'stdItem.ResourceContainer.DefaultComposition', []);

        if (! is_array($defaultComposition) || $defaultComposition === []) {
            $itemData->commodities()->sync([]);

            return;
        }

        $commodityUuids = collect($defaultComposition)
            ->filter(fn (mixed $entry): bool => is_array($entry) && is_string($entry['Entry'] ?? null))
            ->map(fn (array $entry): string => $entry['Entry'])
            ->unique()
            ->values()
            ->all();

        if ($commodityUuids === []) {
            $itemData->commodities()->sync([]);

            return;
        }

        $commodityIds = $this->resolveCommodityIds($commodityUuids);

        $itemData->commodities()->sync($commodityIds);
    }

    private function resolveCommodityIds(array $uuids): array
    {
        if (self::$commodityUuidCache === null) {
            self::$commodityUuidCache = Commodity::query()
                ->pluck('id', 'uuid')
                ->mapWithKeys(fn ($id, $uuid) => [(string) $uuid => (int) $id])
                ->all();
        }

        $ids = [];
        foreach ($uuids as $uuid) {
            $id = self::$commodityUuidCache[$uuid] ?? null;
            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    private function updateSlug(Item $item, string $name): void
    {
        if ($item->slug !== null && $item->slug !== '') {
            return;
        }

        $slugified = Str::slug($name);

        // If the name produces a generic or empty slug, use the unique ID-based fallback
        if ($slugified === '' || $slugified === 'placeholder') {
            $name = "item-{$item->id}";
        }

        app(SlugService::class)->assignUniqueSlug($item, $name, "item-{$item->id}");
    }
}
