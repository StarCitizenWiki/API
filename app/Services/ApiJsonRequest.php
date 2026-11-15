<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundInVersionException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;

class ApiJsonRequest
{
    public function __construct(private readonly Router $router) {}

    /**
     * Fetch a single API resource for a web view, forwarding the given request's
     * query params and injecting the includes. Aborts 404 when the API produced
     * no resource data; surfaces the target instead when the API redirected.
     * Throws NotFoundInVersionException when the API 404 lists other versions.
     *
     * @param  array<int, string>  $includes
     * @return array{data: mixed, meta: array<string, mixed>}|array{__redirect: string}
     *
     * @throws Exception
     */
    public function fetchResource(string $route, Request $request, array $includes = []): array
    {
        $apiRequest = $request->duplicate();

        if ($includes !== []) {
            $apiRequest->query->set('include', implode(',', $includes));
        }

        $payload = $this->request($route, $apiRequest);

        if (isset($payload['__redirect'])) {
            return ['__redirect' => $payload['__redirect']];
        }

        $availableVersions = Arr::get($payload, 'available_versions');

        if (is_array($availableVersions) && $availableVersions !== []) {
            throw new NotFoundInVersionException(
                (string) Arr::get($payload, 'requested_version', ''),
                $availableVersions,
                (string) Arr::get($payload, 'message', 'Not found.'),
            );
        }

        $data = Arr::get($payload, 'data', []);

        if ($data === []) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return ['data' => $data, 'meta' => Arr::get($payload, 'meta', [])];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function request(string $path, Request $request): array
    {
        $originalRequest = app('request');

        $server = $request->server->all();
        $server['REQUEST_URI'] = $path;
        $server['PATH_INFO'] = parse_url($path, PHP_URL_PATH) ?: $path;
        $server['QUERY_STRING'] = parse_url($path, PHP_URL_QUERY) ?: '';

        $parameters = array_merge($request->query->all(), $request->request->all());

        $apiRequest = Request::create(
            $path,
            'GET',
            $parameters,
            $request->cookies->all(),
            [],
            $server
        );
        $apiRequest->headers->set('Accept', 'application/json');
        $apiRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
        $apiRequest->setUserResolver($request->getUserResolver());

        if ($request->hasSession()) {
            $apiRequest->setLaravelSession($request->session());
        }

        app()->instance('request', $apiRequest);
        app('url')->setRequest($apiRequest);

        try {
            $response = $this->router->dispatch($apiRequest);
        } finally {
            app()->instance('request', $originalRequest);
            app('url')->setRequest($originalRequest);
        }

        if ($response->getStatusCode() >= 400) {
            $errorPayload = json_decode($response->getContent() ?? 'null', true);

            return is_array($errorPayload) ? $errorPayload : [];
        }

        if ($response->isRedirection()) {
            return ['__redirect' => $response->headers->get('Location')];
        }

        $payload = json_decode($response->getContent() ?? 'null', true, 512, JSON_THROW_ON_ERROR);

        return is_array($payload) ? $payload : [];
    }
}
