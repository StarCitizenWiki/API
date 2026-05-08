<?php

use App\Support\Game\DeepDiff;

describe('diff', function (): void {
    it('returns empty array for identical scalars', function (): void {
        expect(DeepDiff::diff(42, 42))->toBe([]);
    });

    it('returns empty array for identical strings', function (): void {
        expect(DeepDiff::diff('hello', 'hello'))->toBe([]);
    });

    it('returns empty array for identical nulls', function (): void {
        expect(DeepDiff::diff(null, null))->toBe([]);
    });

    it('detects scalar value change', function (): void {
        expect(DeepDiff::diff(1, 2))->toBe([
            '' => ['old' => 1, 'new' => 2],
        ]);
    });

    it('detects scalar to null', function (): void {
        expect(DeepDiff::diff('old', null))->toBe([
            '' => ['old' => 'old', 'new' => null],
        ]);
    });

    it('detects null to scalar', function (): void {
        expect(DeepDiff::diff(null, 'new'))->toBe([
            '' => ['old' => null, 'new' => 'new'],
        ]);
    });

    it('returns empty array for identical flat objects', function (): void {
        $old = ['a' => 1, 'b' => 2];
        $new = ['a' => 1, 'b' => 2];

        expect(DeepDiff::diff($old, $new))->toBe([]);
    });

    it('detects changed value in flat object', function (): void {
        $old = ['a' => 1, 'b' => 2];
        $new = ['a' => 1, 'b' => 3];

        expect(DeepDiff::diff($old, $new))->toBe([
            'b' => ['old' => 2, 'new' => 3],
        ]);
    });

    it('detects added key in object', function (): void {
        $old = ['a' => 1];
        $new = ['a' => 1, 'b' => 2];

        expect(DeepDiff::diff($old, $new))->toBe([
            'b' => ['old' => null, 'new' => 2],
        ]);
    });

    it('detects removed key in object', function (): void {
        $old = ['a' => 1, 'b' => 2];
        $new = ['a' => 1];

        expect(DeepDiff::diff($old, $new))->toBe([
            'b' => ['old' => 2, 'new' => null],
        ]);
    });

    it('detects nested changes with dot notation paths', function (): void {
        $old = ['stdItem' => ['Mass' => 5, 'Name' => 'Old']];
        $new = ['stdItem' => ['Mass' => 7, 'Name' => 'Old']];

        expect(DeepDiff::diff($old, $new))->toBe([
            'stdItem.Mass' => ['old' => 5, 'new' => 7],
        ]);
    });

    it('detects deeply nested change', function (): void {
        $old = ['a' => ['b' => ['c' => 'old']]];
        $new = ['a' => ['b' => ['c' => 'new']]];

        expect(DeepDiff::diff($old, $new))->toBe([
            'a.b.c' => ['old' => 'old', 'new' => 'new'],
        ]);
    });

    it('detects removed nested key', function (): void {
        $old = ['stdItem' => ['Mass' => 5, 'ItemContainer' => ['Capacity' => 10]]];
        $new = ['stdItem' => ['Mass' => 5]];

        expect(DeepDiff::diff($old, $new))->toBe([
            'stdItem.ItemContainer' => ['old' => ['Capacity' => 10], 'new' => null],
        ]);
    });

    it('detects added nested key', function (): void {
        $old = ['stdItem' => ['Mass' => 5]];
        $new = ['stdItem' => ['Mass' => 5, 'ItemContainer' => ['Capacity' => 10]]];

        expect(DeepDiff::diff($old, $new))->toBe([
            'stdItem.ItemContainer' => ['old' => null, 'new' => ['Capacity' => 10]],
        ]);
    });

    it('detects changes in indexed arrays', function (): void {
        $old = ['tags' => ['a', 'b', 'c']];
        $new = ['tags' => ['a', 'x', 'c']];

        expect(DeepDiff::diff($old, $new))->toBe([
            'tags.1' => ['old' => 'b', 'new' => 'x'],
        ]);
    });

    it('detects added elements to indexed array', function (): void {
        $old = ['tags' => ['a', 'b']];
        $new = ['tags' => ['a', 'b', 'c']];

        expect(DeepDiff::diff($old, $new))->toBe([
            'tags.2' => ['old' => null, 'new' => 'c'],
        ]);
    });

    it('detects removed elements from indexed array', function (): void {
        $old = ['tags' => ['a', 'b', 'c']];
        $new = ['tags' => ['a', 'b']];

        expect(DeepDiff::diff($old, $new))->toBe([
            'tags.2' => ['old' => 'c', 'new' => null],
        ]);
    });

    it('detects scalar to array transition', function (): void {
        $old = ['key' => 'value'];
        $new = ['key' => ['nested' => 'data']];

        expect(DeepDiff::diff($old, $new))->toBe([
            'key' => ['old' => 'value', 'new' => ['nested' => 'data']],
        ]);
    });

    it('detects array to scalar transition', function (): void {
        $old = ['key' => ['nested' => 'data']];
        $new = ['key' => 'value'];

        expect(DeepDiff::diff($old, $new))->toBe([
            'key' => ['old' => ['nested' => 'data'], 'new' => 'value'],
        ]);
    });

    it('handles empty arrays as identical', function (): void {
        expect(DeepDiff::diff([], []))->toBe([]);
    });

    it('handles Laravel Collections', function (): void {
        $old = collect(['stdItem' => collect(['Mass' => 5])]);
        $new = collect(['stdItem' => collect(['Mass' => 7])]);

        expect(DeepDiff::diff($old, $new))->toBe([
            'stdItem.Mass' => ['old' => 5, 'new' => 7],
        ]);
    });

    it('returns empty for identical complex structures', function (): void {
        $data = [
            'stdItem' => [
                'Mass' => 5,
                'Name' => 'Test',
                'Interactions' => ['Sit', 'Exit'],
                'Manufacturer' => ['Code' => 'UNKN', 'Name' => 'Unknown'],
            ],
        ];

        expect(DeepDiff::diff($data, $data))->toBe([]);
    });

    it('detects multiple changes across nested paths', function (): void {
        $old = [
            'stdItem' => ['Mass' => 5, 'Name' => 'Old', 'Grade' => 1],
            'subType' => 'WEAPON',
        ];
        $new = [
            'stdItem' => ['Mass' => 7, 'Name' => 'Old', 'Grade' => 2],
            'subType' => 'WEAPON',
        ];

        $diff = DeepDiff::diff($old, $new);

        expect($diff)->toHaveCount(2)
            ->and($diff)->toHaveKey('stdItem.Mass')
            ->and($diff)->toHaveKey('stdItem.Grade')
            ->and($diff['stdItem.Mass'])->toBe(['old' => 5, 'new' => 7])
            ->and($diff['stdItem.Grade'])->toBe(['old' => 1, 'new' => 2]);
    });
});

describe('diffColumns', function (): void {
    it('returns empty for identical columns', function (): void {
        $old = ['name' => 'Test', 'size' => 1];
        $new = ['name' => 'Test', 'size' => 1];

        expect(DeepDiff::diffColumns($old, $new, ['name', 'size']))->toBe([]);
    });

    it('detects changed columns', function (): void {
        $old = ['name' => 'Old', 'size' => 1, 'grade' => 3];
        $new = ['name' => 'New', 'size' => 1, 'grade' => 3];

        expect(DeepDiff::diffColumns($old, $new, ['name', 'size', 'grade']))->toBe([
            'name' => ['old' => 'Old', 'new' => 'New'],
        ]);
    });

    it('detects multiple changed columns', function (): void {
        $old = ['name' => 'Old', 'size' => 1];
        $new = ['name' => 'New', 'size' => 2];

        expect(DeepDiff::diffColumns($old, $new, ['name', 'size']))->toBe([
            'name' => ['old' => 'Old', 'new' => 'New'],
            'size' => ['old' => 1, 'new' => 2],
        ]);
    });

    it('skips columns that are null in both', function (): void {
        $old = ['name' => 'Test'];
        $new = ['name' => 'Test'];

        expect(DeepDiff::diffColumns($old, $new, ['name', 'missing']))->toBe([]);
    });

    it('detects value to null column change', function (): void {
        $old = ['name' => 'Test', 'type' => 'Weapon'];
        $new = ['name' => 'Test'];

        expect(DeepDiff::diffColumns($old, $new, ['name', 'type']))->toBe([
            'type' => ['old' => 'Weapon', 'new' => null],
        ]);
    });

    it('only compares specified columns', function (): void {
        $old = ['name' => 'Old', 'type' => 'Weapon', 'size' => 1];
        $new = ['name' => 'New', 'type' => 'Armor', 'size' => 1];

        expect(DeepDiff::diffColumns($old, $new, ['name']))->toBe([
            'name' => ['old' => 'Old', 'new' => 'New'],
        ]);
    });
});

describe('buildChangeTree', function (): void {
    it('builds tree from column changes only', function (): void {
        $columns = ['name' => ['old' => 'Old', 'new' => 'New']];
        $tree = DeepDiff::buildChangeTree($columns, []);

        expect($tree)->toBe([
            'name' => ['old' => 'Old', 'new' => 'New'],
        ]);
    });

    it('builds nested tree from flat dot-notation data changes', function (): void {
        $data = ['stdItem.Mass' => ['old' => 5, 'new' => 7]];
        $tree = DeepDiff::buildChangeTree([], $data);

        expect($tree)->toBe([
            'stdItem' => [
                'Mass' => ['old' => 5, 'new' => 7],
            ],
        ]);
    });

    it('merges column and data changes', function (): void {
        $columns = ['name' => ['old' => 'Old', 'new' => 'New']];
        $data = ['stdItem.Mass' => ['old' => 5, 'new' => 7]];
        $tree = DeepDiff::buildChangeTree($columns, $data);

        expect($tree)->toHaveCount(2)
            ->and($tree['name'])->toBe(['old' => 'Old', 'new' => 'New'])
            ->and($tree['stdItem']['Mass'])->toBe(['old' => 5, 'new' => 7]);
    });

    it('expands array values into sub-branches', function (): void {
        $data = ['Container' => ['old' => null, 'new' => ['Capacity' => 10]]];
        $tree = DeepDiff::buildChangeTree([], $data);

        expect($tree['Container']['Capacity'])->toBe(['old' => null, 'new' => 10]);
    });

    it('returns empty tree for empty inputs', function (): void {
        expect(DeepDiff::buildChangeTree([], []))->toBe([]);
    });
});

describe('isLeaf', function (): void {
    it('returns true for leaf nodes', function (): void {
        expect(DeepDiff::isLeaf(['old' => 1, 'new' => 2]))->toBeTrue();
    });

    it('returns false for branch nodes', function (): void {
        expect(DeepDiff::isLeaf(['key' => ['old' => 1, 'new' => 2]]))->toBeFalse();
    });

    it('returns false for scalars', function (): void {
        expect(DeepDiff::isLeaf('string'))->toBeFalse();
    });
});

describe('branchDirection', function (): void {
    it('returns added when all leaves are additions', function (): void {
        $node = ['key' => ['old' => null, 'new' => 'value']];
        expect(DeepDiff::branchDirection($node))->toBe('added');
    });

    it('returns removed when all leaves are removals', function (): void {
        $node = ['key' => ['old' => 'value', 'new' => null]];
        expect(DeepDiff::branchDirection($node))->toBe('removed');
    });

    it('returns mixed for changes', function (): void {
        $node = ['key' => ['old' => 'a', 'new' => 'b']];
        expect(DeepDiff::branchDirection($node))->toBe('mixed');
    });

    it('returns mixed for mixed additions and removals', function (): void {
        $node = [
            'a' => ['old' => null, 'new' => 1],
            'b' => ['old' => 2, 'new' => null],
        ];
        expect(DeepDiff::branchDirection($node))->toBe('mixed');
    });
});

describe('formatValue', function (): void {
    it('formats null as empty string', function (): void {
        expect(DeepDiff::formatValue(null))->toBe('');
    });

    it('formats booleans', function (): void {
        expect(DeepDiff::formatValue(true))->toBe('true')
            ->and(DeepDiff::formatValue(false))->toBe('false');
    });

    it('formats scalars as strings', function (): void {
        expect(DeepDiff::formatValue(42))->toBe('42')
            ->and(DeepDiff::formatValue('hello'))->toBe('hello');
    });

    it('formats arrays as key-value pairs', function (): void {
        expect(DeepDiff::formatValue(['a' => 1, 'b' => 2]))->toBe('a: 1, b: 2');
    });
});
