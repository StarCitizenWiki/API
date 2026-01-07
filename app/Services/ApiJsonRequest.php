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
        $apiRequest = Request::create(
            $path,
            'GET',
            $request->query(),
            $request->cookies->all(),
            [],
            $request->server->all()
        );
        $apiRequest->headers->set('Accept', 'application/json');
        $apiRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
        $apiRequest->setUserResolver($request->getUserResolver());

        if ($request->hasSession()) {
            $apiRequest->setLaravelSession($request->session());
        }

        $response = $this->router->dispatch($apiRequest);

        if ($response->getStatusCode() >= 400) {
            return [];
        }

        $payload = json_decode($response->getContent() ?? 'null', true, 512, JSON_THROW_ON_ERROR);

        return is_array($payload) ? $payload : [];
    }
}
