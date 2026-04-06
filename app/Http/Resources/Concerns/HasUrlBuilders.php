<?php

declare(strict_types=1);

namespace App\Http\Resources\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Trait for building API and web URLs with version support.
 *
 * This trait depends on the base class providing:
 * - urlWithVersion(string $url, Request $request): string
 */
trait HasUrlBuilders
{
    /**
     * Build an API URL for a given slug and route.
     *
     * @param  string  $slug  The identifier (slug, uuid, etc.) for the resource
     * @param  Request  $request  The current request (for version query string)
     * @param  string  $routeName  The named route (e.g., 'resources.locations.index')
     * @param  string  $paramKey  The route parameter key (e.g., 'resource', 'location')
     */
    protected function buildApiUrl(string $slug, Request $request, string $routeName, string $paramKey): string
    {
        return $this->urlWithVersion(
            route($routeName, [$paramKey => $slug]),
            $request
        );
    }

    /**
     * Build a web URL for a given slug and route.
     *
     * Returns empty string if the web route does not exist.
     *
     * @param  string  $slug  The identifier (slug, uuid, etc.) for the resource
     * @param  Request  $request  The current request (for version query string)
     * @param  string  $routeName  The named route (e.g., 'resources.locations.index')
     * @param  string  $paramKey  The route parameter key (e.g., 'resource', 'location')
     */
    protected function buildWebUrl(string $slug, Request $request, string $routeName, string $paramKey): string
    {
        $webRouteName = 'web.'.$routeName;

        if (! Route::has($webRouteName)) {
            return '';
        }

        return $this->urlWithVersion(
            route($webRouteName, [$paramKey => $slug]),
            $request
        );
    }
}
