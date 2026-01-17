<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ColumnSourceMap extends Component
{
    /**
     * @var array<int, array{title: string, field: string}>
     */
    public array $mappings;

    /**
     * @param  array<int, array<string, mixed>>  $columns
     */
    public function __construct(public array $columns)
    {
        $this->mappings = $this->flattenColumns($columns);
    }

    public function render(): View|Closure|string
    {
        return view('components.column-source-map');
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<int, string>  $parents
     * @return array<int, array{title: string, field: string}>
     */
    private function flattenColumns(array $columns, array $parents = []): array
    {
        $mappings = [];

        foreach ($columns as $column) {
            $title = $column['title'] ?? null;
            $nextParents = $parents;

            if (is_string($title) && $title !== '') {
                $nextParents[] = $title;
            }

            if (isset($column['columns']) && is_array($column['columns'])) {
                $mappings = array_merge($mappings, $this->flattenColumns($column['columns'], $nextParents));

                continue;
            }

            $field = $column['field'] ?? null;
            if (! is_string($field) || $field === '') {
                continue;
            }

            $displayTitle = array_filter($nextParents, static fn (string $value): bool => $value !== '');
            $mappings[] = [
                'title' => $displayTitle !== [] ? implode(' / ', $displayTitle) : $field,
                'field' => $field,
            ];
        }

        return $mappings;
    }
}
