@props([
    'item' => null,
    'type' => null,
    'classification' => null,
    'typeFilter' => null,
    'filterQuery' => [],
    'versionParams' => [],
    'breadcrumbs' => [],
])
@php
    use Illuminate\Support\Str;

    if (! is_array($breadcrumbs) || $breadcrumbs === []) {
        if (empty($versionParams)) {
            $resolvedVersionCode = session('game_version_code') ?? request()->query('version');
            $versionParams = $resolvedVersionCode ? ['version' => $resolvedVersionCode] : [];
        }

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

        $subcategoryParentMap = [
            'fps-armor' => 'fps-items',
            'clothes' => 'fps-items',
            'weapons' => 'fps-items',
            'weapon-attachments' => 'fps-items',
            'food' => 'fps-items',
            'medical' => 'fps-items',
            'mining-modifiers' => 'fps-items',
            'vehicle-components' => 'vehicle-items',
            'vehicle-weapons' => 'vehicle-items',
            'vehicle-flair-items' => 'vehicle-items',
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

        $addBreadcrumb = static function (string $label, ?string $url) use (&$breadcrumbs): void {
            $breadcrumbs[] = ['label' => $label, 'url' => $url];
        };

        $makeUrl = static function (array $filter) use ($versionParams): string {
            return route('web.items.index', array_merge($versionParams, ['filter' => $filter]));
        };

        if ($item !== null) {
            $type = data_get($item, 'type');
            $classification = data_get($item, 'classification');
            $normalizedType = ($type !== null && $type !== '') ? str_replace([' ', '-'], '', $type) : null;

            $isShip = str_starts_with($classification ?? '', 'Ship.')
                || in_array($normalizedType, $vehicleComponentTypes, true);
            $category = $isShip ? 'vehicle-items' : 'fps-items';
            $addBreadcrumb($categoryLabelMap[$category], $makeUrl(['category' => $category]));

            $subCategory = null;

            if ($isShip) {
                if (in_array($normalizedType, $vehicleComponentTypes, true)) {
                    $subCategory = 'vehicle-components';
                } elseif (in_array($normalizedType, $vehicleWeaponTypes, true)) {
                    $subCategory = 'vehicle-weapons';
                } elseif (in_array($normalizedType, $vehicleFlairTypes, true)) {
                    $subCategory = 'vehicle-flair-items';
                }
            } else if (in_array($normalizedType, $fpsArmorTypes, true)) {
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
                $label = $subcategoryLabelMap[$subCategory] ?? Str::headline($subCategory);
                $addBreadcrumb($label, $makeUrl(['category' => $subCategory]));
            }

            if ($isShip && $type !== null && $type !== '') {
                $typeLabel = $typePluralMap[$normalizedType] ?? Str::headline($type);
                $addBreadcrumb($typeLabel, $makeUrl(['type' => $type]));
            }

            $addBreadcrumb(data_get($item, 'name', 'Item'), null);
        } else {
            $category = $filterQuery['category'] ?? null;
            $typeFilterValue = $typeFilter;

            if ($category !== null) {
                if (array_key_exists($category, $subcategoryParentMap)) {
                    $parentCategory = $subcategoryParentMap[$category];
                    $addBreadcrumb($categoryLabelMap[$parentCategory], $makeUrl(['category' => $parentCategory]));
                    $addBreadcrumb($subcategoryLabelMap[$category], $makeUrl(['category' => $category]));
                } else {
                    $label = $categoryLabelMap[$category] ?? Str::headline($category);
                    $addBreadcrumb($label, $makeUrl(['category' => $category]));
                }
            }

            if ($typeFilterValue !== null && $typeFilterValue !== '') {
                $filter = [];

                if ($category !== null) {
                    $filter['category'] = $category;
                }

                $filter['type'] = $typeFilterValue;
                $addBreadcrumb(Str::headline($typeFilterValue), $makeUrl($filter));
            }

            if (! empty($filterQuery['sub_type'])) {
                $filter = [];

                if ($category !== null) {
                    $filter['category'] = $category;
                }

                $filter['type'] = $typeFilterValue;
                $filter['sub_type'] = $filterQuery['sub_type'];
                $addBreadcrumb(Str::headline($filterQuery['sub_type']), $makeUrl($filter));
            }

            if (! empty($filterQuery['manufacturer.name'])) {
                $filter = [];

                if ($category !== null) {
                    $filter['category'] = $category;
                }

                $filter['type'] = $typeFilterValue;

                if (! empty($filterQuery['sub_type'])) {
                    $filter['sub_type'] = $filterQuery['sub_type'];
                }

                $filter['manufacturer.name'] = $filterQuery['manufacturer.name'];
                $addBreadcrumb(Str::headline($filterQuery['manufacturer.name']), $makeUrl($filter));
            }
        }
    }
@endphp
<div class="breadcrumbs text-sm text-subtle overflow-x-auto" data-testid="item-breadcrumbs">
    <ul class="w">
        @foreach ($breadcrumbs as $breadcrumb)
            <li>
                @if ($breadcrumb['url'] !== null)
                    <a
                        href="{{ $breadcrumb['url'] }}"
                        data-testid="{{ $loop->first ? 'item-breadcrumbs-all-link' : 'item-breadcrumb-link-'.$loop->index }}"
                    >{{ $breadcrumb['label'] }}</a>
                @else
                    <span>{{ $breadcrumb['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ul>
</div>
