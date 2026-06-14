<?php

declare(strict_types=1);

namespace App\Support\Items;

/**
 * Prepares view data for the {@see resources/views/components/items/hero.blade.php} component.
 *
 * Extracted from the component's inline @php block so the hero's derived data
 * (grade letters, icon mapping, badge assembly, variant state, portrait
 * detection, version-aware URLs) is unit-testable without going through a full
 * HTTP request.
 */
final class ItemHeroViewData
{
    public static function make(): self
    {
        return new self;
    }

    /**
     * @param  array<string, mixed>  $item  Serialized item payload (ItemResource output)
     * @param  string|null  $version  Resolved game version code, or null when unset
     * @return array{
     *     name: string,
     *     wikiUrl: string,
     *     iconName: string,
     *     itemSize: mixed,
     *     headlineLinks: array<int, array{label: mixed, url: string|null}>,
     *     badges: array<int, array{label: string, url: string|null, test_id: string|null, badge_class: string}>,
     *     isPortrait: bool,
     *     hasImage: bool,
     * }
     */
    public function build(array $item, ?string $version = null): array
    {
        $itemName = data_get($item, 'name', 'Item');
        $manufacturerName = data_get($item, 'manufacturer.name');
        $itemType = data_get($item, 'type');
        $itemTypeLabel = data_get($item, 'type_label') ?? $itemType;
        $subTypeLabel = data_get($item, 'sub_type_label') ?? data_get($item, 'sub_type') ?? '';
        $classification = data_get($item, 'classification');
        $classificationValue = is_string($classification) ? $classification : null;

        $typeUrl = data_get($item, 'type_web_url');
        if ($typeUrl === null && $itemType) {
            $typeUrl = $this->buildItemsUrl(['type' => $itemType], $version);
        }

        $manufacturerUrl = $manufacturerName
            ? $this->buildItemsUrl(['manufacturer' => $manufacturerName], $version)
            : null;

        $blueprintUrl = $this->resolveBlueprintUrl($item, $version);

        $variantStateLabel = $this->resolveVariantState($item);

        $gradeLetter = $this->resolveGradeLetter(data_get($item, 'grade'));
        $isCraftable = data_get($item, 'is_craftable') === true;
        $isLootable = data_get($item, 'is_lootable') === true;
        $isFps = $classificationValue !== null && str_starts_with($classificationValue, 'FPS.');

        $headlineLinks = array_values(array_filter([
            $manufacturerName
                ? ['label' => $manufacturerName, 'url' => $manufacturerUrl]
                : null,
            $itemType
                ? ['label' => $itemTypeLabel, 'url' => $typeUrl]
                : null,
            $subTypeLabel
                ? ['label' => $subTypeLabel, 'url' => null]
                : null,
        ]));

        $badges = array_values(array_filter([
            $gradeLetter
                ? ['label' => 'Grade '.$gradeLetter, 'url' => null, 'test_id' => null, 'badge_class' => 'badge-accent badge-outline']
                : null,
            $isCraftable
                ? ['label' => 'Craftable', 'url' => $blueprintUrl, 'test_id' => 'item-hero-pill-craftable', 'badge_class' => 'badge-primary badge-outline']
                : null,
            $isLootable
                ? ['label' => 'Lootable', 'url' => null, 'test_id' => 'item-hero-pill-lootable', 'badge_class' => 'badge-secondary badge-outline']
                : null,
            ($variantStateLabel !== null && $isFps)
                ? ['label' => $variantStateLabel, 'url' => null, 'test_id' => 'item-hero-pill-variant-state', 'badge_class' => 'badge-accent badge-outline']
                : null,
        ]));

        return [
            'name' => $itemName,
            'wikiUrl' => 'https://starcitizen.tools/'.str_replace(' ', '_', $itemName),
            'iconName' => $this->resolveIconName($itemType, $classificationValue),
            'itemSize' => data_get($item, 'size'),
            'headlineLinks' => $headlineLinks,
            'badges' => $badges,
            'isPortrait' => $this->resolvePortrait($classificationValue, data_get($item, 'images', [])),
            'hasImage' => $this->hasImage(data_get($item, 'images', [])),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function buildItemsUrl(array $filters, ?string $version): string
    {
        $url = route('web.items.index', ['filter' => $filters]);

        if ($version !== null && $version !== '') {
            $url = url()->query($url, ['version' => $version]);
        }

        return $url;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function resolveBlueprintUrl(array $item, ?string $version): ?string
    {
        $primaryBlueprint = collect(data_get($item, 'blueprint', []))
            ->first(static fn (mixed $blueprint): bool => is_array($blueprint)
                && is_string(data_get($blueprint, 'uuid'))
                && data_get($blueprint, 'uuid') !== '');

        $blueprintUuid = data_get($primaryBlueprint, 'uuid');
        if (! $blueprintUuid) {
            return null;
        }

        $url = route('web.blueprints.show', ['blueprint' => $blueprintUuid]);

        if ($version !== null && $version !== '') {
            $url = url()->query($url, ['version' => $version]);
        }

        return $url;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function resolveVariantState(array $item): ?string
    {
        $isBaseVariant = data_get($item, 'is_base_variant');
        $currentItemUuid = data_get($item, 'uuid');
        $baseVariant = data_get($item, 'related_items.base_item', []);
        $baseVariantUuid = data_get($baseVariant, 'uuid');
        $relatedVariants = data_get($item, 'related_items.variant_items', []);

        $hasVariantFamily = ($isBaseVariant === false
                && is_string($baseVariantUuid)
                && $baseVariantUuid !== ''
                && $baseVariantUuid !== $currentItemUuid)
            || (is_array($relatedVariants) && $relatedVariants !== []);

        return match (true) {
            $isBaseVariant === false => 'Variant',
            $hasVariantFamily => 'Base Variant',
            default => null,
        };
    }

    /**
     * Ship grades arrive as 1-4 from the resource; map to A-D letters, pass anything else through.
     */
    private function resolveGradeLetter(mixed $grade): mixed
    {
        return match ($grade) {
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            default => $grade,
        };
    }

    private function resolveIconName(mixed $itemType, ?string $classificationValue): string
    {
        return match (true) {
            $itemType === 'PowerPlant' => 'power',
            $itemType === 'Shield' || str_contains($classificationValue ?? '', 'Shield') => 'shield',
            $itemType === 'QuantumDrive' => 'atom',
            $itemType === 'JumpDrive' => 'egg-fried',
            $itemType === 'Cooler' => 'fan',
            $itemType === 'Radar' => 'radar',
            $itemType === 'Missile' || $itemType === 'MissileLauncher' => 'rocket',
            $itemType === 'Armor' || str_contains($classificationValue ?? '', 'Armor') => 'shield-check',
            str_contains($classificationValue ?? '', 'Weapon') || str_contains($classificationValue ?? '', 'FPS') => 'crosshair',
            str_starts_with($classificationValue ?? '', 'Ship.') => 'rocket',
            default => 'box',
        };
    }

    /**
     * @param  array<int, mixed>  $images
     */
    private function hasImage(mixed $images): bool
    {
        if (! is_array($images) || ! isset($images[0]) || ! is_array($images[0])) {
            return false;
        }

        return is_string(data_get($images[0], 'thumbnail_url'))
            || is_string(data_get($images[0], 'original_url'));
    }

    /**
     * @param  array<int, mixed>  $images
     */
    private function resolvePortrait(?string $classificationValue, mixed $images): bool
    {
        if ($classificationValue !== null && str_starts_with($classificationValue, 'FPS.')) {
            return true;
        }

        $image = is_array($images) && isset($images[0]) && is_array($images[0]) ? $images[0] : [];
        $width = (int) (data_get($image, 'thumbnail_width') ?? data_get($image, 'original_width') ?? 0);
        $height = (int) (data_get($image, 'thumbnail_height') ?? data_get($image, 'original_height') ?? 0);

        return $width > 0 && $height > 0 && $height > $width;
    }
}
