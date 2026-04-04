<?php

declare(strict_types=1);

namespace App\Support\Starmap;

final class StarmapLocationTableConfig
{
    /**
     * @return array{
     *     title:string,
     *     columns:array<int, array<string, mixed>>,
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
                'type.name' => 'type_name',
                'type.classification' => 'type_classification',
                'respawn_location_type' => 'respawn_location_type',
                'jurisdiction.name' => 'jurisdiction_name',
                'affiliation.name' => 'affiliation_name',
                'amenities' => 'amenity',
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
                    'title' => 'Type',
                    'field' => 'type.name',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'sortField' => 'type_name',
                    'minWidth' => 160,
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
