<?php

declare(strict_types=1);

use App\Http\Requests\Api\Game\SearchRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

it('normalizes search query values through the form request lifecycle', function (): void {
    Route::post('/test/search-request-normalization', function (SearchRequest $request): JsonResponse {
        return response()->json([
            'query' => $request->validated('query'),
        ]);
    });

    $this->postJson('/test/search-request-normalization', [
        'query' => '  Sol_System  ',
    ])
        ->assertSuccessful()
        ->assertExactJson([
            'query' => 'Sol System',
        ]);
});

it('does not double decode percent sequences in search queries', function (): void {
    Route::get('/test/search-request-decoding', function (SearchRequest $request): JsonResponse {
        return response()->json([
            'query' => $request->validated('query'),
        ]);
    });

    // A client searching for the literal text "50%2F50%2B" sends %25-encoded sequences;
    // PHP already decodes the query string once, so the request must not decode again.
    $this->getJson('/test/search-request-decoding?query=50%252F50%252B')
        ->assertSuccessful()
        ->assertExactJson([
            'query' => '50%2F50%2B',
        ]);
});

it('rejects search query values that normalize to empty strings', function (): void {
    Route::post('/test/search-request-normalization-empty', function (SearchRequest $request): JsonResponse {
        return response()->json([
            'query' => $request->validated('query'),
        ]);
    });

    $this->postJson('/test/search-request-normalization-empty', [
        'query' => '___',
    ])
        ->assertUnprocessable()
        ->assertInvalid([
            'query' => 'A search query is required.',
        ]);
});
