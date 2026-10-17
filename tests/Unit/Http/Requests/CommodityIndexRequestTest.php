<?php

declare(strict_types=1);

use App\Http\Requests\Api\Game\CommodityIndexRequest;
use Illuminate\Validation\ValidationException;

function makeCommodityIndexRequest(mixed $used = null, bool $hasUsed = true): CommodityIndexRequest
{
    $filter = $hasUsed ? ['used' => $used] : ['name' => 'Agricium'];

    $request = CommodityIndexRequest::create('/api/commodities', 'GET', ['filter' => $filter]);
    $request->setContainer(app())->setRedirector(app('redirect'));

    return $request;
}

it('casts the used filter to a boolean before validation', function (mixed $input, bool $expected): void {
    $request = makeCommodityIndexRequest($input);

    $request->validateResolved();

    expect($request->validated())->toBe(['filter' => ['used' => $expected]]);
})->with([
    'string true' => ['true', true],
    'string false' => ['false', false],
    'string 1' => ['1', true],
    'string 0' => ['0', false],
    'bool true' => [true, true],
    'bool false' => [false, false],
]);

it('rejects an unrecognizable used filter', function (): void {
    $request = makeCommodityIndexRequest('garbage');

    expect(fn () => $request->validateResolved())
        ->toThrow(ValidationException::class);
});

it('ignores a missing used filter', function (): void {
    $request = makeCommodityIndexRequest(hasUsed: false);

    $request->validateResolved();

    expect($request->input('filter'))->toBe(['name' => 'Agricium'])
        ->and($request->validated())->toBe([]);
});
