<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ItemShowSeoData extends AbstractShowSeoData
{
    /**
     * @return array<string, mixed>
     */
    public function build(array $item, Request $request): array
    {
        $itemName = $this->normalizeString(data_get($item, 'name')) ?? 'Item';
        $type = $this->normalizeString(data_get($item, 'type'));
        $manufacturerName = $this->normalizeString(data_get($item, 'manufacturer.name'));
        $classification = $this->normalizeString(data_get($item, 'classification'));
        $itemClass = $this->normalizeString(data_get($item, 'class'));
        $grade = $this->formatGrade(data_get($item, 'grade'));
        $uuid = $this->normalizeString(data_get($item, 'uuid'));
        $description = $this->resolveDescription(data_get($item, 'description'));
        $size = $this->normalizeScalar(data_get($item, 'size'));
        $version = $this->resolveVersionCode($request);
        $canonicalUrl = $this->normalizeString(data_get($item, 'web_url'))
            ?? $this->fallbackShowUrl($uuid, $version);
        $breadcrumbs = $this->buildBreadcrumbs($item, $canonicalUrl, $version);
        $isShipComponent = $this->isShipComponent($type, $classification);
        $metaTitle = $this->buildMetaTitle(
            itemName: $itemName,
            manufacturerName: $manufacturerName,
            type: $type,
            classification: $classification,
            size: $size,
            itemClass: $itemClass,
            grade: $grade,
            isShipComponent: $isShipComponent,
        );
        $metaDescription = Str::limit(
            $description ?? $this->buildFallbackDescription(
                itemName: $itemName,
                manufacturerName: $manufacturerName,
                type: $type,
                classification: $classification,
                size: $size,
                itemClass: $itemClass,
                grade: $grade,
                isShipComponent: $isShipComponent,
            ),
            160,
        );
        $category = $this->resolveLeafBreadcrumbLabel($breadcrumbs)
            ?? $classification
            ?? $type
            ?? 'Star Citizen Item';

        return [
            'title' => $metaTitle,
            'metaDescription' => $metaDescription,
            'canonicalUrl' => $canonicalUrl,
            'keywords' => $this->compactValues([
                $itemName,
                $type,
                $manufacturerName,
                $classification,
                $size !== null ? 'Size '.$size : null,
                $itemClass,
                $grade !== null ? 'Grade '.$grade : null,
                'Star Citizen',
                'SC',
            ]),
            'ogTitle' => $metaTitle,
            'ogDescription' => $metaDescription,
            'twitterTitle' => $metaTitle,
            'twitterDescription' => $metaDescription,
            'breadcrumbs' => $breadcrumbs,
            'structuredData' => $this->compactValues([
                $this->buildBreadcrumbStructuredData($breadcrumbs),
                $this->buildEntityStructuredData(
                    itemName: $itemName,
                    manufacturerName: $manufacturerName,
                    category: $category,
                    metaDescription: $metaDescription,
                    canonicalUrl: $canonicalUrl,
                    uuid: $uuid,
                    type: $type,
                    classification: $classification,
                    size: $size,
                    itemClass: $itemClass,
                    grade: $grade,
                    version: $this->normalizeString(data_get($item, 'version')),
                ),
            ]),
        ];
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private function buildBreadcrumbs(array $item, string $canonicalUrl, ?string $version): array
    {
        $versionParams = $version !== null ? ['version' => $version] : [];
        $breadcrumbs = [[
            'label' => 'All Items',
            'url' => route('web.items.index', $versionParams),
        ]];

        $categoryLabelMap = [
            'fps-items' => 'FPS Items',
            'vehicle-items' => 'Vehicle Items',
        ];

        $subcategoryLabelMap = [
            'fps-armor' => 'Armor',
            'clothes' => 'Clothing',
            'weapons' => 'Personal Weapons',
            'weapon-attachments' => 'Weapon Attachments',
            'food' => 'Food',
            'medical' => 'Medical',
            'mining-modifiers' => 'Mining Modifiers',
            'vehicle-components' => 'Components',
            'vehicle-weapons' => 'Vehicle Weapons',
            'vehicle-flair-items' => 'Vehicle Flair Items',
        ];

        $vehicleComponentTypes = ['Cooler', 'PowerPlant', 'QuantumDrive', 'Shield'];
        $vehicleWeaponTypes = ['WeaponGun'];
        $vehicleFlairTypes = ['Flair_Cockpit', 'Flair_Wall', 'Flair_Floor', 'Flair_Surface'];
        $fpsArmorTypes = [
            'Char_Armor_Undersuit',
            'Char_Armor_Arms',
            'Char_Armor_Helmet',
            'Char_Armor_Torso',
            'Char_Armor_Legs',
        ];

        $fpsTypeToSubcategory = [
            'WeaponPersonal' => 'weapons',
            'WeaponAttachment' => 'weapon-attachments',
            'Food' => 'food',
            'Bottle' => 'food',
            'Drink' => 'food',
        ];

        $classificationSubcategoryMap = [
            'FPS.Armor' => 'fps-armor',
            'FPS.Clothing' => 'clothes',
            'FPS.Consumable.Medical' => 'medical',
            'Mining.' => 'mining-modifiers',
        ];

        $typePluralMap = [
            'Cooler' => 'Coolers',
            'PowerPlant' => 'Power-Plants',
            'QuantumDrive' => 'Quantum Drives',
            'Shield' => 'Shields',
            'ShieldController' => 'Shield Controllers',
            'JumpDrive' => 'Jump Drives',
            'FlightController' => 'Flight Controllers',
            'Radar' => 'Radars',
            'SelfDestruct' => 'Self-Destructs',
            'Turret' => 'Turrets',
            'MissileLauncher' => 'Missile Racks',
            'WeaponGun' => 'Hardpoint Weapons',
            'WeaponDefensive' => 'Countermeasures',
        ];

        $makeUrl = static function (array $filter) use ($versionParams): string {
            return route('web.items.index', array_merge($versionParams, ['filter' => $filter]));
        };

        $type = $this->normalizeString(data_get($item, 'type'));
        $classification = $this->normalizeString(data_get($item, 'classification'));
        $normalizedType = $type !== null ? str_replace([' ', '-'], '', $type) : null;
        $isShip = $this->isShipComponent($type, $classification);
        $category = $isShip ? 'vehicle-items' : 'fps-items';

        $breadcrumbs[] = [
            'label' => $categoryLabelMap[$category],
            'url' => $makeUrl(['category' => $category]),
        ];

        $subCategory = null;

        if ($isShip) {
            if ($normalizedType !== null && in_array($normalizedType, $vehicleComponentTypes, true)) {
                $subCategory = 'vehicle-components';
            } elseif ($normalizedType !== null && in_array($normalizedType, $vehicleWeaponTypes, true)) {
                $subCategory = 'vehicle-weapons';
            } elseif ($normalizedType !== null && in_array($normalizedType, $vehicleFlairTypes, true)) {
                $subCategory = 'vehicle-flair-items';
            }
        } elseif ($normalizedType !== null && in_array($normalizedType, $fpsArmorTypes, true)) {
            $subCategory = 'fps-armor';
        } elseif ($normalizedType !== null && array_key_exists($normalizedType, $fpsTypeToSubcategory)) {
            $subCategory = $fpsTypeToSubcategory[$normalizedType];
        } elseif ($classification !== null) {
            foreach ($classificationSubcategoryMap as $prefix => $mappedSubcategory) {
                if (str_starts_with($classification, $prefix)) {
                    $subCategory = $mappedSubcategory;
                    break;
                }
            }
        }

        if ($subCategory !== null) {
            $breadcrumbs[] = [
                'label' => $subcategoryLabelMap[$subCategory] ?? Str::headline($subCategory),
                'url' => $makeUrl(['category' => $subCategory]),
            ];
        }

        if ($isShip && $type !== null) {
            $breadcrumbs[] = [
                'label' => $typePluralMap[$normalizedType] ?? Str::headline($type),
                'url' => $makeUrl(['type' => $type]),
            ];
        }

        $breadcrumbs[] = [
            'label' => $this->normalizeString(data_get($item, 'name')) ?? 'Item',
            'url' => $canonicalUrl,
        ];

        return $breadcrumbs;
    }

    private function buildMetaTitle(
        string $itemName,
        ?string $manufacturerName,
        ?string $type,
        ?string $classification,
        string|int|float|null $size,
        ?string $itemClass,
        ?string $grade,
        bool $isShipComponent,
    ): string {
        $leading = $manufacturerName !== null
            ? $itemName.' by '.$manufacturerName
            : $itemName;

        if ($isShipComponent) {
            $detail = $this->joinSegments([
                $type,
                $size !== null ? 'Size '.$size : null,
                $itemClass,
                $grade !== null ? 'Grade '.$grade : null,
            ]);
        } else {
            $detail = $this->joinSegments([$type, $classification]);
        }

        if ($detail === '') {
            $detail = $type !== null ? $type.' Item' : 'Item';
        }

        return $this->joinSegments([$leading, $detail, 'Star Citizen'], ' | ');
    }

    private function buildFallbackDescription(
        string $itemName,
        ?string $manufacturerName,
        ?string $type,
        ?string $classification,
        string|int|float|null $size,
        ?string $itemClass,
        ?string $grade,
        bool $isShipComponent,
    ): string {
        $base = $type !== null
            ? 'Browse Star Citizen '.$type.' data for '.$itemName
            : 'Browse Star Citizen item data for '.$itemName;

        if ($manufacturerName !== null) {
            $base .= ' by '.$manufacturerName;
        }

        $attributes = $isShipComponent
            ? $this->compactValues([
                $size !== null ? 'size '.$size : null,
                $itemClass !== null ? 'class '.$itemClass : null,
                $grade !== null ? 'grade '.$grade : null,
            ])
            : $this->compactValues([
                $classification !== null ? 'classification '.$classification : null,
            ]);

        if ($attributes !== []) {
            $base .= ', '.implode(', ', $attributes);
        }

        $base .= $isShipComponent
            ? '. View ports, variants, and technical details.'
            : '. View description, related items, and technical details.';

        return $base;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEntityStructuredData(
        string $itemName,
        ?string $manufacturerName,
        string $category,
        string $metaDescription,
        string $canonicalUrl,
        ?string $uuid,
        ?string $type,
        ?string $classification,
        string|int|float|null $size,
        ?string $itemClass,
        ?string $grade,
        ?string $version,
    ): array {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $itemName,
            'description' => $metaDescription,
            'url' => $canonicalUrl,
            'category' => $category,
        ];

        if ($manufacturerName !== null) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name' => $manufacturerName,
            ];
        }

        if ($uuid !== null) {
            $schema['sku'] = $uuid;
        }

        $additionalProperty = $this->buildPropertyValues([
            'Type' => $type,
            'Classification' => $classification,
            'Size' => $size,
            'Class' => $itemClass,
            'Grade' => $grade,
            'Version' => $version,
        ]);

        if ($additionalProperty !== []) {
            $schema['additionalProperty'] = $additionalProperty;
        }

        return $schema;
    }

    private function isShipComponent(?string $type, ?string $classification): bool
    {
        return str_starts_with($classification ?? '', 'Ship.')
            || in_array($type, ['Cooler', 'PowerPlant', 'QuantumDrive', 'Shield'], true);
    }

    private function formatGrade(mixed $grade): ?string
    {
        if ($grade === null || $grade === '') {
            return null;
        }

        return match ($grade) {
            1, '1' => 'A',
            2, '2' => 'B',
            3, '3' => 'C',
            4, '4' => 'D',
            default => $this->normalizeString((string) $grade),
        };
    }

    protected function fallbackShowUrl(?string $uuid, ?string $version): string
    {
        if ($uuid === null) {
            return url()->current();
        }

        return route('web.items.show', array_filter([
            'item' => $uuid,
            'version' => $version,
        ]));
    }

    /**
     * @param  array<int, array{label: string, url: string}>  $breadcrumbs
     */
    private function resolveLeafBreadcrumbLabel(array $breadcrumbs): ?string
    {
        if (count($breadcrumbs) < 2) {
            return null;
        }

        return $this->normalizeString($breadcrumbs[count($breadcrumbs) - 2]['label'] ?? null);
    }
}
