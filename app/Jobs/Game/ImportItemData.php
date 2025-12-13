<?php

namespace App\Jobs\Game;

use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\ItemDescriptionData;
use App\Models\Game\ItemTranslation;
use App\Models\Game\Manufacturer;
use App\Models\System\Language;
use App\Services\Parser\SC\Labels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;

class ImportItemData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private ?Labels $labels = null;

    public function __construct(
        private readonly int $gameVersionId,
        private readonly string $path
    ) {}

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

        $item = Item::query()->firstOrCreate(
            ['uuid' => $uuid],
            ['uuid' => $uuid]
        );

        $manufacturerId = $this->resolveManufacturerId($itemPayload, $uuid);

        $itemData = ItemData::query()->updateOrCreate(
            [
                'item_id' => $item->id,
                'game_version_id' => $this->gameVersionId,
            ],
            $this->mapItemData($itemPayload, $manufacturerId)
        );

        $raw = $payload['Raw'] ?? [];

        $this->syncDescriptionData($item, $itemPayload, $raw);
        $this->syncTranslations($itemData, $itemPayload, $raw);
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
        $manufacturerUuid = Arr::get($itemPayload, 'stdItem.Manufacturer.UUID');

        $manufacturer = Manufacturer::query()
            ->where('uuid', $manufacturerUuid)
            ->first();

        if ($manufacturer === null) {
            throw new RuntimeException(sprintf('Manufacturer with uuid %s does not exist for item %s.', $manufacturerUuid, $uuid));
        }

        return $manufacturer->id;
    }

    private function mapItemData(array $itemPayload, int $manufacturerId): array
    {
        $name = $this->extractName($itemPayload);

        return [
            'manufacturer_id' => $manufacturerId,
            'name' => $name,
            'type' => $itemPayload['type'] ?? null,
            'sub_type' => $itemPayload['subType'] ?? null,
            'classification' => $itemPayload['classification'] ?? null,
            'size' => $this->nullableInt($itemPayload['size'] ?? null),
            'grade' => $this->nullableInt($itemPayload['grade'] ?? null),
            'class_name' => $itemPayload['className'] ?? null,
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

    private function syncTranslations(ItemData $itemData, array $itemPayload, array $raw): void
    {
        $english = $this->extractEnglishDescription($raw, $itemPayload);
        $descriptionLabel = $this->extractDescriptionLabel($raw);

        if ($english !== null && $english !== '') {
            ItemTranslation::query()->updateOrCreate(
                [
                    'item_data_id' => $itemData->id,
                    'locale_code' => Language::ENGLISH,
                ],
                [
                    'translation' => $english,
                ]
            );
        }

        if ($descriptionLabel === null) {
            return;
        }

        $chinese = $this->fetchChineseTranslation($descriptionLabel);

        if ($chinese === null || $chinese === '') {
            return;
        }

        ItemTranslation::query()->updateOrCreate(
            [
                'item_data_id' => $itemData->id,
                'locale_code' => Language::CHINESE,
            ],
            [
                'translation' => $this->getDescriptionText($chinese),
            ]
        );
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

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function fetchChineseTranslation(string $label): ?string
    {
        $normalized = ltrim($label, '@');

        return $this->getLabels()->getDataZh()->get($normalized);
    }

    private function getLabels(): Labels
    {
        if ($this->labels === null) {
            $this->labels = new Labels;
        }

        return $this->labels;
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
}
