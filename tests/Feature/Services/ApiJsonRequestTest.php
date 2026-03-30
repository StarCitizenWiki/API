<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\ApiJsonRequest;
use Illuminate\Http\JsonResponse;
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

it('restores the original request and url generator request after dispatching', function (): void {
    Route::get('/api/test-api-request-restoration', function (): JsonResponse {
        return response()->json([
            'ok' => true,
        ]);
    });

    Route::get('/web/test-api-request-restoration', function (Request $request, ApiJsonRequest $apiJsonRequest): JsonResponse {
        $originalRequest = app('request');

        $payload = $apiJsonRequest->request('/api/test-api-request-restoration', $request);

        return response()->json([
            'payload' => $payload,
            'request_restored' => app('request') === $originalRequest,
            'url_request_restored' => app('url')->getRequest() === $originalRequest,
        ]);
    });

    $this->get('/web/test-api-request-restoration')
        ->assertSuccessful()
        ->assertJsonPath('payload.ok', true)
        ->assertJsonPath('request_restored', true)
        ->assertJsonPath('url_request_restored', true);
});

it('propagates authenticated user to the nested api request', function (): void {
    Route::get('/api/test-api-user-propagation', function (Request $request): JsonResponse {
        return response()->json([
            'user_id' => $request->user()?->id,
            'auth_id' => auth()->id(),
        ]);
    });

    Route::get('/web/test-api-user-propagation', function (Request $request, ApiJsonRequest $apiJsonRequest): JsonResponse {
        return response()->json($apiJsonRequest->request('/api/test-api-user-propagation', $request));
    });

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/web/test-api-user-propagation')
        ->assertSuccessful()
        ->assertJsonPath('user_id', $user->id)
        ->assertJsonPath('auth_id', $user->id);
});

it('propagates session state to the nested api request', function (): void {
    Route::get('/api/test-api-session-propagation', function (Request $request): JsonResponse {
        if ($request->hasSession()) {
            $request->session()->put('nested_touched', true);
        }

        return response()->json([
            'has_session' => $request->hasSession(),
            'marker' => $request->hasSession() ? $request->session()->get('marker') : null,
        ]);
    });

    Route::middleware('web')->get('/web/test-api-session-propagation', function (Request $request, ApiJsonRequest $apiJsonRequest): JsonResponse {
        return response()->json($apiJsonRequest->request('/api/test-api-session-propagation', $request));
    });

    $response = $this->withSession(['marker' => 'from-session'])
        ->get('/web/test-api-session-propagation');

    $response->assertSuccessful()
        ->assertJsonPath('has_session', true)
        ->assertJsonPath('marker', 'from-session')
        ->assertSessionHas('nested_touched', true);
});

it('forwards merged query and request parameters to the nested api request', function (): void {
    Route::get('/api/test-api-merged-params', function (Request $request): JsonResponse {
        return response()->json([
            'method' => $request->method(),
            'query' => $request->query->all(),
        ]);
    });

    Route::post('/web/test-api-merged-params', function (Request $request, ApiJsonRequest $apiJsonRequest): JsonResponse {
        return response()->json($apiJsonRequest->request('/api/test-api-merged-params?foo=from-path&path_only=yes', $request));
    });

    $this->post('/web/test-api-merged-params?foo=from-query&bar=from-query', [
        'bar' => 'from-body',
        'baz' => 'from-body',
    ])
        ->assertSuccessful()
        ->assertJsonPath('method', 'GET')
        ->assertJsonPath('query.foo', 'from-query')
        ->assertJsonPath('query.path_only', 'yes')
        ->assertJsonPath('query.bar', 'from-body')
        ->assertJsonPath('query.baz', 'from-body');
});
