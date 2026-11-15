<?php

declare(strict_types=1);

namespace App\Jobs\Game\Concerns;

use App\Models\Game\Manufacturer;
use App\Models\System\Language;
use App\Services\Parser\SC\Labels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

trait ImportsScUnpackedPayload
{
    private static ?Labels $labels = null;

    private static ?Collection $manufacturerLookup = null;

    private function readPayload(): array
    {
        $contents = Storage::disk($this->diskName)->get($this->path);

        if (! is_string($contents) || trim($contents) === '') {
            return [];
        }

        return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    }

    private function resolveManufacturerId(array $payload, string $uuidPath, string $entityLabel): int
    {
        $manufacturerUuid = Arr::get($payload, $uuidPath, '00000000-0000-0000-0000-000000000000');

        if (self::$manufacturerLookup === null) {
            self::$manufacturerLookup = Manufacturer::query()->get(['id', 'uuid'])->keyBy('uuid');
        }

        $manufacturer = self::$manufacturerLookup->get($manufacturerUuid);

        if ($manufacturer === null) {
            // Manufacturers may have been created since the cache was loaded.
            self::$manufacturerLookup = Manufacturer::query()->get(['id', 'uuid'])->keyBy('uuid');
            $manufacturer = self::$manufacturerLookup->get($manufacturerUuid);
        }

        if ($manufacturer === null) {
            throw new RuntimeException(sprintf('Manufacturer with uuid %s does not exist for %s.', $manufacturerUuid, $entityLabel));
        }

        return $manufacturer->id;
    }

    private function upsertVersionedRow(string $table, string $fkColumn, int $fkId, array $values): void
    {
        $updateColumns = $values
            |> array_keys(...)
            |> (static fn ($x) => array_diff($x, [$fkColumn, 'game_version_id']))
            |> array_values(...);

        $now = now();
        $row = array_map(
            static fn (mixed $value): mixed => is_array($value) ? json_encode($value, JSON_THROW_ON_ERROR) : $value,
            $values,
        ) + [
            $fkColumn => $fkId,
            'game_version_id' => $this->gameVersionId,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::table($table)->upsert([$row], [$fkColumn, 'game_version_id'], [...$updateColumns, 'updated_at']);
    }

    private function getLabels(): Labels
    {
        if (self::$labels === null) {
            self::$labels = new Labels;
        }

        return self::$labels;
    }

    /** The model must use HasTranslations. */
    private function syncDescriptionTranslations(Model $model, ?string $english, ?string $labelKey): void
    {
        if ($labelKey === null || trim($labelKey) === '') {
            return;
        }

        $updated = false;
        if ($english !== null && trim($english) !== '') {
            $model->setTranslation('translation', Language::ENGLISH, trim($english));
            $updated = true;
        }

        foreach (config('translations.locales', []) as $locale) {
            $translation = $this->getLabels()->getTranslation($locale, $labelKey);
            if ($translation !== null && $translation !== '') {
                $model->setTranslation('translation', $locale, str_replace('\n', "\n", $translation));
                $updated = true;
            }
        }

        if ($updated) {
            $model->save();
        }
    }
}
