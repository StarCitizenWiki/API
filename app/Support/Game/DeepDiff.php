<?php

declare(strict_types=1);

namespace App\Support\Game;

use Illuminate\Support\Collection;

class DeepDiff
{
    /**
     * Compute a deep recursive diff between two values.
     * Returns a flat map of dot-notation paths to ['old' => ..., 'new' => ...].
     * Only paths that actually changed are included.
     *
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public static function diff(mixed $old, mixed $new): array
    {
        $changes = [];
        static::compute($old, $new, '', $changes);

        return $changes;
    }

    /**
     * Diff scalar columns between two models/arrays.
     * Only includes keys where the value actually changed.
     *
     * @param  array<string, mixed>  $columns  Column names to compare
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public static function diffColumns(array $old, array $new, array $columns): array
    {
        $changes = [];

        foreach ($columns as $column) {
            $oldValue = $old[$column] ?? null;
            $newValue = $new[$column] ?? null;

            if ($oldValue !== $newValue) {
                $changes[$column] = ['old' => $oldValue, 'new' => $newValue];
            }
        }

        return $changes;
    }

    /**
     * Build a nested tree from flat column_changes and data_changes.
     * Column changes appear as top-level leaves. Data changes are nested by dot-notation path.
     *
     * @param  array<string, mixed>  $columnChanges
     * @param  array<string, mixed>  $dataChanges
     * @return array<string, mixed>
     */
    public static function buildChangeTree(array $columnChanges, array $dataChanges): array
    {
        $tree = array_map(static function ($change) {
            return ['old' => $change['old'] ?? null, 'new' => $change['new'] ?? null];
        }, $columnChanges);

        foreach ($dataChanges as $path => $change) {
            $parts = explode('.', $path);
            $node = &$tree;

            foreach ($parts as $i => $part) {
                if ($i === count($parts) - 1) {
                    $old = is_array($change) && array_key_exists('old', $change) ? $change['old'] : null;
                    $new = is_array($change) && array_key_exists('new', $change) ? $change['new'] : null;
                    $node[$part] = static::expandLeaf($old, $new);
                } else {
                    if (! isset($node[$part]) || ! is_array($node[$part]) || array_key_exists('old', $node[$part])) {
                        $node[$part] = [];
                    }

                    $node = &$node[$part];
                }
            }

            unset($node);
        }

        return $tree;
    }

    /**
     * Determine the overall direction of a change-tree branch.
     * Returns 'added', 'removed', or 'mixed'.
     */
    public static function branchDirection(array $node): string
    {
        $hasAdded = false;
        $hasRemoved = false;
        $hasChanged = false;

        foreach ($node as $value) {
            if (static::isLeaf($value)) {
                $oldNull = $value['old'] === null;
                $newNull = $value['new'] === null;

                if ($oldNull && ! $newNull) {
                    $hasAdded = true;
                } elseif (! $oldNull && $newNull) {
                    $hasRemoved = true;
                } elseif (! $oldNull && ! $newNull) {
                    $hasChanged = true;
                }
            } else {
                $dir = static::branchDirection($value);

                match ($dir) {
                    'added' => $hasAdded = true,
                    'removed' => $hasRemoved = true,
                    default => $hasChanged = true,
                };
            }

            if (($hasAdded || $hasRemoved) && $hasChanged) {
                return 'mixed';
            }

            if ($hasAdded && $hasRemoved) {
                return 'mixed';
            }
        }

        if ($hasChanged) {
            return 'mixed';
        }

        if ($hasAdded) {
            return 'added';
        }

        if ($hasRemoved) {
            return 'removed';
        }

        return 'mixed';
    }

    /** Format a value for display in the change tree. */
    public static function formatValue(mixed $val): string
    {
        if ($val === null) {
            return '';
        }

        if (is_bool($val)) {
            return $val ? 'true' : 'false';
        }

        if (is_array($val)) {
            return collect($val)->map(function ($v, $k) {
                $formatted = is_array($v) ? json_encode($v) : (string) $v;

                return is_int($k) ? $formatted : "$k: $formatted";
            })->implode(', ');
        }

        return (string) $val;
    }

    /** Check if a tree node is a leaf (has 'old'/'new' keys). */
    public static function isLeaf(mixed $value): bool
    {
        return is_array($value) && array_key_exists('old', $value);
    }

    /**
     * Expand leaf values into sub-leaves when they are arrays.
     *
     * @return array<string, mixed>
     */
    private static function expandLeaf(mixed $old, mixed $new): array
    {
        $oldIsAssoc = is_array($old) && ! array_is_list($old);
        $newIsAssoc = is_array($new) && ! array_is_list($new);
        $oldIsList = is_array($old) && array_is_list($old);
        $newIsList = is_array($new) && array_is_list($new);

        if (! $oldIsAssoc && ! $newIsAssoc && ! $oldIsList && ! $newIsList) {
            return ['old' => $old, 'new' => $new];
        }

        // If old has content but new is an empty array, the whole thing was
        // removed — return a simple leaf (normalized new to null) so the
        // template renders it as a crossed-out key rather than expanding into
        // meaningless #0/#1 entries.
        if (($oldIsAssoc || $oldIsList) && is_array($new) && $new === []) {
            return ['old' => $old, 'new' => null];
        }

        if ($oldIsAssoc || $newIsAssoc) {
            $keys = array_unique(array_merge(
                $oldIsAssoc ? array_keys($old) : [],
                $newIsAssoc ? array_keys($new) : [],
            ));

            $branch = [];

            foreach ($keys as $key) {
                $oldVal = $oldIsAssoc && array_key_exists($key, $old) ? $old[$key] : null;
                $newVal = $newIsAssoc && array_key_exists($key, $new) ? $new[$key] : null;
                $branch[$key] = static::expandLeaf($oldVal, $newVal);
            }

            return $branch;
        }

        if ($oldIsList || $newIsList) {
            return static::expandIndexedList($old, $new);
        }
    }

    /**
     * Expand indexed arrays into #0, #1, ... branches, pairing by index.
     *
     * @return array<string, mixed>
     */
    private static function expandIndexedList(mixed $old, mixed $new): array
    {
        $oldList = is_array($old) ? $old : [];
        $newList = is_array($new) ? $new : [];
        $max = max(count($oldList), count($newList));

        $branch = [];

        for ($i = 0; $i < $max; $i++) {
            $oldItem = $oldList[$i] ?? null;
            $newItem = $newList[$i] ?? null;
            $branch["#{$i}"] = static::expandLeaf($oldItem, $newItem);
        }

        return $branch;
    }

    /**
     * @param  array<string, array{old: mixed, new: mixed}>  $changes
     */
    private static function compute(mixed $old, mixed $new, string $path, array &$changes): void
    {
        if ($old instanceof Collection) {
            $old = $old->toArray();
        }

        if ($new instanceof Collection) {
            $new = $new->toArray();
        }

        if (static::isAssociative($old) && static::isAssociative($new)) {
            static::diffObject($old, $new, $path, $changes);

            return;
        }

        if (is_array($old) && is_array($new) && ! static::isAssociative($old) && ! static::isAssociative($new)) {
            static::diffIndexedArray($old, $new, $path, $changes);

            return;
        }

        if ((is_array($old) || is_array($new)) && $old !== $new) {
            $changes[$path] = ['old' => $old, 'new' => $new];

            return;
        }

        if ($old !== $new) {
            $changes[$path] = ['old' => $old, 'new' => $new];
        }
    }

    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     * @param  array<string, array{old: mixed, new: mixed}>  $changes
     */
    private static function diffObject(array $old, array $new, string $path, array &$changes): void
    {
        $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($allKeys as $key) {
            $childPath = $path === '' ? $key : "{$path}.{$key}";
            $oldHas = array_key_exists($key, $old);
            $newHas = array_key_exists($key, $new);

            if (! $oldHas && $newHas) {
                $changes[$childPath] = ['old' => null, 'new' => $new[$key]];

                continue;
            }

            if ($oldHas && ! $newHas) {
                $changes[$childPath] = ['old' => $old[$key], 'new' => null];

                continue;
            }

            static::compute($old[$key], $new[$key], $childPath, $changes);
        }
    }

    /**
     * @param  array<int, mixed>  $old
     * @param  array<int, mixed>  $new
     * @param  array<string, array{old: mixed, new: mixed}>  $changes
     */
    private static function diffIndexedArray(array $old, array $new, string $path, array &$changes): void
    {
        $max = max(count($old), count($new));

        for ($i = 0; $i < $max; $i++) {
            $childPath = "{$path}.{$i}";
            $oldHas = array_key_exists($i, $old);
            $newHas = array_key_exists($i, $new);

            if (! $oldHas && $newHas) {
                $changes[$childPath] = ['old' => null, 'new' => $new[$i]];

                continue;
            }

            if ($oldHas && ! $newHas) {
                $changes[$childPath] = ['old' => $old[$i], 'new' => null];

                continue;
            }

            static::compute($old[$i], $new[$i], $childPath, $changes);
        }
    }

    /** Check if an array is associative (has string keys). */
    private static function isAssociative(mixed $value): bool
    {
        if (! is_array($value) || $value === []) {
            return false;
        }

        return ! array_is_list($value);
    }
}
