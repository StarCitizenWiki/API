<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class ApiJsonRequest
{
    public function __construct(private readonly Router $router) {}

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
            return [];
        }

        $payload = json_decode($response->getContent() ?? 'null', true, 512, JSON_THROW_ON_ERROR);

        return is_array($payload) ? $payload : [];
    }
}
