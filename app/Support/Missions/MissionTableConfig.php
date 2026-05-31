<?php

declare(strict_types=1);

namespace App\Support\Missions;

final class MissionTableConfig
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

        $legalityFilterOptions = [
            ['value' => '', 'label' => 'All'],
            ['value' => 'false', 'label' => 'Legal'],
            ['value' => 'true', 'label' => 'Illegal'],
        ];

        return [
            'title' => 'Missions',
            'headerFilterOptionsMap' => [
                'title' => 'query',
                'star_systems' => 'star_system',
                'faction.name' => 'faction',
                'reward_scope' => 'reward_scope',
                'has_blueprints' => 'has_blueprints',
                'blueprint_name' => 'blueprint_name',
                'rank_index' => 'rank_index',
                'has_combat' => 'has_combat',
                'has_prerequisites' => 'has_prerequisites',
                'shareable' => 'shareable',
                'reputation_scope' => 'reputation_scope',
                'grouped' => 'grouped',
            ],
            'externalFilters' => [
                [
                    'title' => 'Grouping',
                    'field' => 'grouped',
                    'options' => [
                        ['value' => '', 'label' => 'Auto'],
                        ['value' => 'true', 'label' => 'Grouped'],
                        ['value' => 'false', 'label' => 'All variants'],
                    ],
                ],
                [
                    'title' => 'Blueprint',
                    'field' => 'blueprint_name',
                ],
                [
                    'title' => 'Legality',
                    'field' => 'illegal',
                    'options' => $legalityFilterOptions,
                ],
                [
                    'title' => 'Reputation Scope',
                    'field' => 'reputation_scope',
                ],
                [
                    'title' => 'Shareable',
                    'field' => 'shareable',
                    'options' => $booleanFilterOptions,
                ],
            ],
            'columns' => [
                [
                    'title' => 'Title',
                    'field' => 'title',
                    'formatter' => 'link',
                    'formatterParams' => [
                        'labelField' => 'title',
                        'urlField' => 'web_url',
                    ],
                    'headerFilter' => 'input',
                    'headerSort' => true,
                    'minWidth' => 280,
                    'frozen' => true,
                ],
                [
                    'title' => 'System',
                    'field' => 'star_systems',
                    'headerFilter' => 'list',
                    'headerSort' => false,
                ],
                [
                    'title' => 'Faction',
                    'field' => 'faction.name',
                    'headerFilter' => 'list',
                    'headerSort' => false,
                ],
                [
                    'title' => 'Category',
                    'field' => 'reward_scope',
                    'headerFilter' => 'list',
                    'headerSort' => false,
                ],
                [
                    'title' => 'Blueprints',
                    'field' => 'has_blueprints',
                    'formatter' => 'tickCross',
                    'formatterParams' => [
                        'allowEmpty' => true,
                    ],
                    'headerFilter' => 'list',
                    'headerSort' => false,
                    'hozAlign' => 'center',
                ],
                [
                    'title' => 'Blueprint Items',
                    'field' => 'blueprints',
                    'formatter' => 'labelList',
                    'formatterParams' => [
                        'labelField' => 'name',
                    ],
                    'headerSort' => false,
                    'width' => 200,
                ],
                [
                    'title' => 'Reward',
                    'field' => 'reward_min',
                    'formatter' => 'money',
                    'formatterParams' => [
                        'symbol' => 'aUEC',
                        'symbolAfter' => true,
                        'precision' => false,
                    ],
                    'sorter' => 'number',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                ],
                [
                    'title' => 'XP',
                    'field' => 'reputation_amount',
                    'sorter' => 'number',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                ],
                [
                    'title' => 'Legality',
                    'field' => 'legality_label',
                    'headerSort' => false,
                    'hozAlign' => 'center',
                ],
                [
                    'title' => 'Rank',
                    'field' => 'rank_index',
                    'sorter' => 'number',
                    'headerSort' => true,
                    'headerFilter' => 'list',
                    'hozAlign' => 'right',
                ],
                [
                    'title' => 'Time (min)',
                    'field' => 'time_to_complete_minutes',
                    'sorter' => 'number',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                ],
                [
                    'title' => 'Max Players',
                    'field' => 'max_players_per_instance',
                    'sorter' => 'number',
                    'headerSort' => true,
                    'hozAlign' => 'right',
                ],
                [
                    'title' => 'Combat',
                    'field' => 'has_combat',
                    'formatter' => 'tickCross',
                    'formatterParams' => [
                        'allowEmpty' => true,
                    ],
                    'headerFilter' => 'list',
                    'headerSort' => false,
                    'hozAlign' => 'center',
                ],
                [
                    'title' => 'Prerequisites',
                    'field' => 'has_prerequisites',
                    'formatter' => 'tickCross',
                    'formatterParams' => [
                        'allowEmpty' => true,
                    ],
                    'headerFilter' => 'list',
                    'headerSort' => false,
                    'hozAlign' => 'center',
                ],
                [
                    'title' => 'Released',
                    'field' => 'released',
                    'formatter' => 'tickCross',
                    'formatterParams' => [
                        'allowEmpty' => true,
                        'cross' => true,
                    ],
                    'headerSort' => false,
                    'hozAlign' => 'center',
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
                ],
            ],
        ];
    }
}
