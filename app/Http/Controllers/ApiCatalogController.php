<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ApiCatalogController
{
    public function __invoke(): JsonResponse
    {
        return response()
            ->json([
                'linkset' => [
                    [
                        'anchor' => url('/api'),
                        'link' => [
                            [
                                'href' => url('/api/openapi'),
                                'rel' => 'service-desc',
                                'type' => 'application/vnd.oai.openapi',
                            ],
                            [
                                'href' => url('/developers'),
                                'rel' => 'service-doc',
                                'type' => 'text/html',
                            ],
                            [
                                'href' => url('/up'),
                                'rel' => 'status',
                                'type' => 'text/html',
                            ],
                        ],
                    ],
                ],
            ])
            ->header('Content-Type', 'application/linkset+json')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
