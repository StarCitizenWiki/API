<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Support\QueryIdentifier;

it('selects the identifier column without changing the value', function (string $identifier, string $column): void {
    $query = Blueprint::query();

    QueryIdentifier::whereUuidOrSlug($query, $identifier);

    expect($query->getQuery()->wheres[0])->toMatchArray(
        ['column' => $column, 'operator' => '=', 'value' => $identifier],
    )->and($query->getBindings())->toBe([$identifier]);
})->with([
    ['123e4567-e89b-12d3-a456-426614174000', 'uuid'],
    ['some-slug', 'slug'],
    ['', 'slug'],
]);
