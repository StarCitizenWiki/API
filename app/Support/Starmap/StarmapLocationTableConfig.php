<?php

declare(strict_types=1);

namespace App\Support\Starmap;

final class StarmapLocationTableConfig
{
    /**
     * @return array{
     *     title:string,
     *     columns:array<int, array<string, mixed>>,
     *     externalFilters:array<int, array{title:string, field:string, options?:array<int, array{value:string, label:string}>}>,
     *     headerFilterOptionsMap:array<string, string>
     * }
     */
    public function build(): array
    {
        $booleanFilterValues = [
            '' => 'All',
            'true' => 'Yes',
            'false' => 'No',
        ];

        return [
            'title' => 'Starmap Locations',
            'headerFilterOptionsMap' => [
                'system' => 'system',
                'parent.name' => 'parent_name',
                'type.classification' => 'type_classification',
                'respawn_location_type' => 'respawn_location_type',
                'jurisdiction.name' => 'jurisdiction_name',
                'affiliation.name' => 'affiliation_name',
                'amenities' => 'amenity',
                'has_resources' => 'has_resources',
                'resources' => 'resource',
                'hide_minor_locations' => 'hide_minor_locations',
            ],
            'externalFilters' => [
                [
                    'title' => 'Hide Minor Locations',
                    'field' => 'hide_minor_locations',
                    'options' => [
                        ['value' => '', 'label' => 'All'],
                        ['value' => 'true', 'label' => 'Yes'],
                    ],
                ],
                [
                    'title' => 'Has Mineables',
                    'field' => 'has_resources',
                    'options' => [
                        ['value' => '', 'label' => 'All'],
                        ['value' => 'true', 'label' => 'Yes'],
                        ['value' => 'false', 'label' => 'No'],
                    ],
                ],
                [
                    'title' => 'Available Commodities',
                    'field' => 'resources',
                ],
            ],
            'columns' => [
                [
                    'title' => 'Name',
                    'field' => 'name',
                    'headerSort' => true,
                    'headerFilter' => 'input',
                    'minWidth' => 240,
                    'frozen' => true,
                    'formatter' => 'link',
                    'formatterParams' => [
                        'labelField' => 'name',
                        'target' => 'blank',
                        'urlField' => 'web_url',
                    ],
                ],
                [
                    'title' => 'System',
                    'field' => 'system',
                    'headerSort' => false,
                    'headerFilter' => 'list',
                    'minWidth' => 180,
                ],
                [
                    'title' => 'Parent',
                    'field' => 'parent.name',
                    'headerSort' => false,
                    'headerFilter' => 'list',
                    'minWidth' => 180,
                ],
                [
                    'title' => 'Classification',
                    'field' => 'type.classification',
                    'headerSort' => false,
                    'headerFilter' => 'list',
                    'minWidth' => 180,
                ],
                [
                    'title' => 'Respawn',
                    'field' => 'respawn_location_type',
                    'headerSort' => false,
                    'headerFilter' => 'list',
                    'minWidth' => 160,
                ],
                [
                    'title' => 'Jurisdiction',
                    'field' => 'jurisdiction.name',
                    'headerSort' => false,
                    'headerFilter' => 'list',
                    'minWidth' => 180,
                ],
                [
                    'title' => 'Affiliation',
                    'field' => 'affiliation.name',
                    'headerSort' => false,
                    'headerFilter' => 'list',
                    'minWidth' => 180,
                ],
                [
                    'title' => 'Children',
                    'field' => 'child_count',
                    'sorter' => 'number',
                    'sortField' => 'child_count',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                    'width' => 120,
                ],
                [
                    'title' => 'Amenities',
                    'field' => 'amenities',
                    'formatter' => 'labelList',
                    'formatterParams' => [
                        'labelField' => 'display_name',
                        'fallbackField' => 'name',
                    ],
                    'headerSort' => false,
                    'headerFilter' => 'list',
                    'minWidth' => 220,
                    'maxWidth' => '80%',
                ],
                [
                    'title' => 'Scannable',
                    'field' => 'is_scannable',
                    'formatter' => 'tickCross',
                    'headerSort' => false,
                    'headerFilter' => 'list',
                    'headerFilterParams' => [
                        'values' => $booleanFilterValues,
                    ],
                    'width' => 120,
                ],
                [
                    'title' => 'Block Travel',
                    'field' => 'block_travel',
                    'formatter' => 'tickCross',
                    'headerSort' => false,
                    'headerFilter' => 'list',
                    'headerFilterParams' => [
                        'values' => $booleanFilterValues,
                    ],
                    'width' => 130,
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
