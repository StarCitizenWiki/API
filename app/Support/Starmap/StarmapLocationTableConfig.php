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
                'system_name' => 'system_name',
                'parent_name' => 'parent_name',
                'type_name' => 'type_name',
                'type_classification' => 'type_classification',
                'respawn_location_type' => 'respawn_location_type',
                'jurisdiction_name' => 'jurisdiction_name',
                'affiliation_name' => 'affiliation_name',
                'amenities_label' => 'amenity',
                'tag_name' => 'tag',
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
                        'urlField' => 'link',
                    ],
                ],
                [
                    'title' => 'System',
                    'field' => 'system_name',
                    'headerSort' => false,
                    'headerFilter' => 'list',
                    'minWidth' => 180,
                ],
                [
                    'title' => 'Parent',
                    'field' => 'parent_name',
                    'headerSort' => false,
                    'headerFilter' => 'list',
                    'minWidth' => 180,
                ],
                [
                    'title' => 'Type',
                    'field' => 'type_name',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'minWidth' => 160,
                ],
                [
                    'title' => 'Classification',
                    'field' => 'type_classification',
                    'headerSort' => true,
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
                    'title' => 'Respawn',
                    'field' => 'respawn_location_type',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'minWidth' => 150,
                ],
                [
                    'title' => 'Jurisdiction',
                    'field' => 'jurisdiction_name',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'minWidth' => 180,
                ],
                [
                    'title' => 'Affiliation',
                    'field' => 'affiliation_name',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'minWidth' => 180,
                ],
                [
                    'title' => 'Amenities',
                    'field' => 'amenities_label',
                    'headerSort' => false,
                    'headerFilter' => 'list',
                    'minWidth' => 220,
                ],
                [
                    'title' => 'Tag',
                    'field' => 'tag_name',
                    'headerSort' => false,
                    'headerFilter' => 'input',
                    'minWidth' => 180,
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
                    'title' => 'Hide Map',
                    'field' => 'hide_in_starmap',
                    'formatter' => 'tickCross',
                    'headerSort' => false,
                    'headerFilter' => 'list',
                    'headerFilterParams' => [
                        'values' => $booleanFilterValues,
                    ],
                    'width' => 120,
                ],
                [
                    'title' => 'Hide World',
                    'field' => 'hide_in_world',
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
                    'title' => 'Prison',
                    'field' => 'jurisdiction_is_prison',
                    'formatter' => 'tickCross',
                    'headerSort' => false,
                    'headerFilter' => 'list',
                    'headerFilterParams' => [
                        'values' => $booleanFilterValues,
                    ],
                    'width' => 110,
                ],
                [
                    'title' => 'Size',
                    'field' => 'size',
                    'sorter' => 'number',
                    'sortField' => 'size',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                    'width' => 110,
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
