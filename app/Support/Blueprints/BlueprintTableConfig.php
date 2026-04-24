<?php

declare(strict_types=1);

namespace App\Support\Blueprints;

final class BlueprintTableConfig
{
    /**
     * @return array{
     *     title:string,
     *     pageSize:int,
     *     columns:array<int,array<string,mixed>>,
     *     headerFilterOptionsMap: array<string, string>
     * }
     */
    public function build(): array
    {
        return [
            'title' => 'Blueprints',
            'pageSize' => 25,
            'headerFilterOptionsMap' => [
                'output.type_label' => 'output.type',
                'is_available_by_default' => 'default',
                'ingredients' => 'ingredient.uuid',
            ],
            'columns' => [
                [
                    'title' => 'Output',
                    'field' => 'output.name',
                    'headerFilter' => 'input',
                    'minWidth' => 240,
                    'frozen' => true,
                    'formatter' => 'link',
                    'formatterParams' => [
                        'labelField' => 'output.name',
                        'target' => 'blank',
                        'urlField' => 'web_url',
                    ],
                ],
                [
                    'title' => 'Type',
                    'field' => 'output.type_label',
                    'headerFilter' => 'list',
                    'minWidth' => 170,
                ],
                [
                    'title' => 'Craft Time (s)',
                    'field' => 'craft_time_seconds',
                    'sortField' => 'craft_time_seconds',
                    'headerSort' => true,
                    'sorter' => 'number',
                    'hozAlign' => 'right',
                    'width' => 140,
                ],
                [
                    'title' => 'Ingredients',
                    'field' => 'ingredient_count',
                    'sortField' => 'ingredient_count',
                    'headerSort' => true,
                    'sorter' => 'number',
                    'hozAlign' => 'right',
                    'width' => 130,
                ],
                [
                    'title' => 'Missions',
                    'field' => 'unlocking_missions_count',
                    'sortField' => 'unlocking_missions_count',
                    'headerSort' => true,
                    'sorter' => 'number',
                    'hozAlign' => 'right',
                    'width' => 120,
                ],
                [
                    'title' => 'Inputs / Dismantle',
                    'field' => 'ingredients',
                    'formatter' => 'labelList',
                    'formatterParams' => [
                        'labelField' => 'name',
                        'fallbackField' => 'name',
                    ],
                    'headerFilter' => 'list',
                    'minWidth' => 180,
                ],
                [
                    'title' => 'Default',
                    'field' => 'is_available_by_default',
                    'formatter' => 'tickCross',
                    'headerFilter' => 'list',
                    'headerFilterParams' => [
                        'values' => [
                            '' => 'All',
                            'true' => 'Yes',
                            'false' => 'No',
                        ],
                    ],
                    'hozAlign' => 'center',
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
