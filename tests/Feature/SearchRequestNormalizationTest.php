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
