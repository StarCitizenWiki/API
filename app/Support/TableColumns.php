<?php

declare(strict_types=1);

namespace App\Support;

final class TableColumns
{
    public static function apiUrlColumn(?int $width = 100): array
    {
        $column = [
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
        ];

        if ($width !== null) {
            $column['width'] = $width;
        }

        return $column;
    }

    public static function booleanOptions(string $emptyLabel = 'All'): array
    {
        return [
            ['value' => '', 'label' => $emptyLabel],
            ['value' => 'true', 'label' => 'Yes'],
            ['value' => 'false', 'label' => 'No'],
        ];
    }
}
