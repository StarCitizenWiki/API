<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\File;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class OpenApiController
{
    #[OA\Get(
        path: '/api/openapi',
        operationId: 'getOpenApiSpec',
        description: 'Returns the OpenAPI 3.0 specification as YAML. This spec describes all documented API endpoints, parameters, request bodies, and response schemas.',
        summary: 'OpenAPI Specification',
        tags: ['Meta'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OpenAPI specification in YAML format',
                content: new OA\MediaType(
                    mediaType: 'application/yaml',
                    schema: new OA\Schema(type: 'string', format: 'binary'),
                ),
            ),
        ],
    )]
    public function __invoke(): Response
    {
        return response(
            File::get(base_path('swagger.yaml'))
        )->header('Content-Type', 'application/yaml');
    }
}
