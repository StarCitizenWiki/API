<?php

declare(strict_types=1);

use App\Services\GalactapediaClient;
use App\Services\RsiDownloadClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('sends GraphQL variables and returns data', function (): void {
    Http::fake(['*' => Http::response(['data' => ['Article' => ['id' => '42']]])]);

    $client = new GalactapediaClient(new RsiDownloadClient);
    expect($client->query('query Article($id: ID!) { Article(id: $id) { id } }', ['id' => '42']))
        ->toBe(['Article' => ['id' => '42']]);

    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), 'galactapedia/graphql')
        && $request['variables'] === ['id' => '42']);
});

it('returns null for missing data and omits empty variables', function (): void {
    Http::fake(['*' => Http::response(['errors' => [['message' => 'error']]])]);

    $client = new GalactapediaClient(new RsiDownloadClient);
    expect($client->query('query { allArticle { edges { node { id } } } }'))->toBeNull();

    Http::assertSent(fn (Request $request): bool => ! array_key_exists('variables', $request->data()));
});
