<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Account\User\User;
use Dingo\Api\Auth\Provider\Authorization;
use Dingo\Api\Routing\Route;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Simple Auth Provider vor Tokens
 */
class DingoTokenAuthProvider extends Authorization
{
    private const API_TOKEN = 'api_token';

    /**
     * Authenticate the request and return the authenticated user instance.
     *
     * @param Request $request
     * @param Route   $route
     *
     * @return User
     *
     * @throws UnauthorizedHttpException
     */
    public function authenticate(Request $request, Route $route)
    {
        if (!$request->has(self::API_TOKEN)) {
            $this->validateAuthorizationHeader($request);
        }

        try {
            $user = User::where(
                self::API_TOKEN,
                $request->bearerToken() ?? $request->get(self::API_TOKEN)
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedHttpException($this->getAuthorizationMethod(), 'Invalid credentials.', $e);
        }

        return $user;
    }

    /**
     * Get the providers authorization method.
     *
     * @return string
     */
    public function getAuthorizationMethod()
    {
        return 'bearer';
    }
}
