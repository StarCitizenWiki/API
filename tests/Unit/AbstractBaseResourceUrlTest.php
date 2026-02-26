<?php

declare(strict_types=1);

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

it('adds default metadata on construction', function (): void {
    $resource = new AbstractBaseResourceStub(['id' => 1]);

    expect($resource->additional)->toHaveKey('meta')
        ->and($resource->additional['meta']['valid_relations'])->toBe(['foo', 'bar'])
        ->and($resource->additional['meta']['processed_at'])->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
});

it('merges metadata arrays recursively', function (): void {
    $resource = new AbstractBaseResourceStub(['id' => 1]);

    $resource->addMetadata('filters', [
        'type' => 'ship',
        'sort' => 'name',
    ]);

    $resource->addMetadata('filters', [
        'type' => 'vehicle',
        'limit' => 10,
    ]);

    expect($resource->additional['meta']['filters'])->toBe([
        'type' => 'vehicle',
        'sort' => 'name',
        'limit' => 10,
    ]);
});

it('merges metadata when given an array payload', function (): void {
    $resource = new AbstractBaseResourceStub(['id' => 1]);

    $resource->addMetadata([
        'request' => [
            'locale' => 'en',
        ],
    ]);

    $resource->addMetadata([
        'request' => [
            'page' => 2,
        ],
    ]);

    expect($resource->additional['meta']['request'])->toBe([
        'locale' => 'en',
        'page' => 2,
    ]);
});

class AbstractBaseResourceStub extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return ['foo', 'bar'];
    }

    public function toArray(Request $request): array
    {
        return (array) $this->resource;
    }
}
