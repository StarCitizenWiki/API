<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/user',
    operationId: 'getAuthenticatedUser',
    description: 'Returns the currently authenticated user. Requires a valid Sanctum bearer token.',
    summary: 'Get Authenticated User',
    security: [
        ['sanctum' => []],
    ],
    tags: ['Auth'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'The authenticated user',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'is_admin', type: 'boolean', example: false),
                    new OA\Property(property: 'language_id', type: 'integer', nullable: true, example: 1),
                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
                ],
                type: 'object'
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated.',
            content: new OA\JsonContent(ref: '#/components/schemas/unauthenticated_error_response'),
        ),
    ],
)]
final class UserController extends Controller
{
    public function __invoke(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();

        return $user;
    }
}
