<?php

declare(strict_types=1);

use App\Support\QueryLike;
use Illuminate\Database\Eloquent\Builder;

it('ignores empty and non-string filter values', function (mixed $value): void {
    $query = Mockery::mock(Builder::class);
    $query->shouldNotReceive('whereLike', 'where');

    QueryLike::filter('name')($query, $value);
})->with(['', null, 123, [['name']]]);

it('escapes wildcard characters in a single-column filter', function (): void {
    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('whereLike')->once()->with('name', '%a\%b\_c\\\\d%');

    QueryLike::filter('name')($query, 'a%b_c\\d');
});

it('groups multiple columns with the same escaped pattern', function (): void {
    $query = Mockery::mock(Builder::class);
    $nested = Mockery::mock(Builder::class);
    $nested->shouldReceive('orWhereLike')->once()->with('name', '%a\%%');
    $nested->shouldReceive('orWhereLike')->once()->with('description', '%a\%%');
    $query->shouldReceive('where')->once()->with(Mockery::on(function ($callback) use ($nested): bool {
        $callback($nested);

        return true;
    }));

    QueryLike::filter(['name', 'description'])($query, 'a%');
});
