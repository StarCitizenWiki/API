<?php

declare(strict_types=1);

namespace App\Support\Resources;

final class CommodityTableConfig
{
    /**
     * @return array{
     *     title: string,
     *     columns: array<int, array<string, mixed>>,
     *     externalFilters: array<int, array{title: string, field: string, options?: array<int, array{value: string, label: string}>}>,
     *     headerFilterOptionsMap: array<string, string>,
     * }
     */
    public function build(): array
    {
        $booleanFilterOptions = [
            ['value' => '', 'label' => 'All'],
            ['value' => 'true', 'label' => 'Yes'],
            ['value' => 'false', 'label' => 'No'],
        ];

        return [
            'title' => 'Commodities',
            'headerFilterOptionsMap' => [
                'tier' => 'rarity',
                'kind' => 'kind',
                'systems' => 'system',
                'locations' => 'location',
                'name' => 'query',
                'refined_version_name' => 'refined_version',
                'is_mineable' => 'mineable',
                'has_ship_mineables' => 'ship',
                'has_ground_vehicle_mineables' => 'ground_vehicle',
                'has_fps_mineables' => 'fps',
                'has_harvestables' => 'harvestable',
                'used' => 'used',
            ],
            'externalFilters' => [
                [
                    'title' => 'Mineable',
                    'field' => 'is_mineable',
                    'options' => $booleanFilterOptions,
                ],
                [
                    'title' => 'Ship Mining',
                    'field' => 'has_ship_mineables',
                    'options' => $booleanFilterOptions,
                ],
                [
                    'title' => 'Vehicle Mining',
                    'field' => 'has_ground_vehicle_mineables',
                    'options' => $booleanFilterOptions,
                ],
                [
                    'title' => 'FPS Mining',
                    'field' => 'has_fps_mineables',
                    'options' => $booleanFilterOptions,
                ],
                [
                    'title' => 'Harvestable',
                    'field' => 'has_harvestables',
                    'options' => $booleanFilterOptions,
                ],
                [
                    'title' => 'In Blueprints',
                    'field' => 'used',
                    'options' => $booleanFilterOptions,
                ],
            ],
            'columns' => [
                [
                    'title' => 'Commodity',
                    'field' => 'name',
                    'headerFilter' => 'input',
                    'headerSort' => true,
                    'minWidth' => 240,
                    'frozen' => true,
                    'formatter' => 'link',
                    'formatterParams' => [
                        'labelField' => 'name',
                        'urlField' => 'web_url',
                    ],
                ],
                [
                    'title' => 'Rarity',
                    'field' => 'tier',
                    'headerFilter' => 'list',
                    'headerSort' => true,
                ],
                [
                    'title' => 'Refines To',
                    'field' => 'refined_version_name',
                    'headerFilter' => 'list',
                    'headerSort' => true,
                ],
                [
                    'title' => 'Signature',
                    'field' => 'signature',
                    'sorter' => 'number',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                ],
                [
                    'title' => 'Kind',
                    'field' => 'kind',
                    'headerFilter' => 'list',
                    'headerSort' => false,
                ],
                [
                    'title' => 'Systems',
                    'field' => 'systems',
                    'headerFilter' => 'list',
                    'headerSort' => false,
                ],
                [
                    'title' => 'Locations',
                    'field' => 'locations',
                    'formatter' => 'labelList',
                    'formatterParams' => [
                        'labelField' => 'name',
                        'fallbackField' => 'name',
                    ],
                    'headerFilter' => 'list',
                    'headerSort' => false,
                    'width' => 280,
                ],
                [
                    'title' => 'Density',
                    'field' => 'density_g_per_cc',
                    'sorter' => 'number',
                    'sortField' => 'density',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                ],
                [
                    'title' => 'Instability',
                    'field' => 'instability',
                    'sorter' => 'number',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                ],
                [
                    'title' => 'Resistance',
                    'field' => 'resistance',
                    'sorter' => 'number',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                ],
                [
                    'title' => 'Box Sizes (SCU)',
                    'field' => 'box_sizes_scu',
                    'headerSort' => false,
                ],
                [
                    'title' => 'API Url',
                    'field' => 'uuid',
                    'formatter' => 'link',
                    'formatterParams' => [
                        'label' => 'API Url',
                        'target' => 'blank',
                        'urlField' => 'link',
                    ],
                    'headerSort' => false,
                    'hozAlign' => 'right',
                    'width' => 100,
                ],
            ],
        ];
    }
}
