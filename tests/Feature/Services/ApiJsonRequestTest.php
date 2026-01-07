<?php

declare(strict_types=1);

use App\Services\ApiJsonRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

it('returns the api payload with forwarded query params', function (): void {
    Route::get('/api/test-api', function (Request $request) {
        return response()->json([
            'data' => [
                'foo' => $request->query('foo'),
            ],
        ]);
    });

    Route::get('/web/test-proxy', function (Request $request, ApiJsonRequest $apiJsonRequest) {
        return response()->json($apiJsonRequest->request('/api/test-api', $request));
    });

    $this->get('/web/test-proxy?foo=bar')
        ->assertSuccessful()
        ->assertJson([
            'data' => [
                'foo' => 'bar',
            ],
        ]);
});

it('returns an empty array for error responses', function (): void {
    Route::get('/api/test-api-error', function () {
        return response()->json(['message' => 'missing'], 404);
    });

    Route::get('/web/test-proxy-error', function (Request $request, ApiJsonRequest $apiJsonRequest) {
        return response()->json($apiJsonRequest->request('/api/test-api-error', $request));
    });

    $this->get('/web/test-proxy-error')
        ->assertSuccessful()
        ->assertExactJson([]);
});
