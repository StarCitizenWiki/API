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

it('fetches a resource with data, meta, includes and forwarded query params', function (): void {
    Route::get('/api/test-fetch-resource', function (Request $request): JsonResponse {
        return response()->json([
            'data' => [
                'id' => 7,
                'include' => $request->query('include'),
                'foo' => $request->query('foo'),
            ],
            'meta' => ['current_page' => 1],
        ]);
    });

    Route::get('/web/test-fetch-resource', function (Request $request, ApiJsonRequest $apiJsonRequest): JsonResponse {
        return response()->json($apiJsonRequest->fetchResource('/api/test-fetch-resource', $request, ['images', 'links']));
    });

    $this->get('/web/test-fetch-resource?foo=bar')
        ->assertSuccessful()
        ->assertJsonPath('data.id', 7)
        ->assertJsonPath('data.include', 'images,links')
        ->assertJsonPath('data.foo', 'bar')
        ->assertJsonPath('meta.current_page', 1);
});

it('aborts 404 when the fetched resource has no data', function (): void {
    Route::get('/api/test-fetch-resource-empty', function (): JsonResponse {
        return response()->json(['data' => [], 'meta' => []]);
    });

    Route::get('/web/test-fetch-resource-empty', function (Request $request, ApiJsonRequest $apiJsonRequest): JsonResponse {
        return response()->json($apiJsonRequest->fetchResource('/api/test-fetch-resource-empty', $request));
    });

    $this->get('/web/test-fetch-resource-empty')->assertNotFound();
});

it('aborts 404 when the fetched api endpoint errors', function (): void {
    Route::get('/api/test-fetch-resource-error', function (): JsonResponse {
        return response()->json(['message' => 'missing'], 404);
    });

    Route::get('/web/test-fetch-resource-error', function (Request $request, ApiJsonRequest $apiJsonRequest): JsonResponse {
        return response()->json($apiJsonRequest->fetchResource('/api/test-fetch-resource-error', $request));
    });

    $this->get('/web/test-fetch-resource-error')->assertNotFound();
});

it('surfaces an api redirect envelope instead of aborting', function (): void {
    Route::get('/api/test-fetch-resource-redirect', function () {
        return redirect()->to('/api/vehicles/some-uuid', 302);
    });

    Route::get('/web/test-fetch-resource-redirect', function (Request $request, ApiJsonRequest $apiJsonRequest): JsonResponse {
        return response()->json($apiJsonRequest->fetchResource('/api/test-fetch-resource-redirect', $request, ['images']));
    });

    $this->get('/web/test-fetch-resource-redirect')
        ->assertSuccessful()
        ->assertExactJson(['__redirect' => url('/api/vehicles/some-uuid')]);
});

it('keeps the request include param untouched when no includes are given', function (): void {
    Route::get('/api/test-fetch-resource-include-passthrough', function (Request $request): JsonResponse {
        return response()->json([
            'data' => ['include' => $request->query('include')],
        ]);
    });

    Route::get('/web/test-fetch-resource-include-passthrough', function (Request $request, ApiJsonRequest $apiJsonRequest): JsonResponse {
        return response()->json($apiJsonRequest->fetchResource('/api/test-fetch-resource-include-passthrough', $request));
    });

    $this->get('/web/test-fetch-resource-include-passthrough?include=user')
        ->assertSuccessful()
        ->assertJsonPath('data.include', 'user');
});
