<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MigrateLimitParameter
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $query = $request->query;
        $queryValues = $query->all();
        $page = $queryValues['page'] ?? null;
        $queryString = (string) $request->server->get('QUERY_STRING', '');

        $pageParams = [];

        if (is_numeric($page)) {
            $pageNumber = max(1, (int) $page);
            $pageParams['number'] = $pageNumber;
        }

        if (is_array($page)) {
            $pageParams = $page;

            if (isset($pageParams['number']) && is_numeric($pageParams['number'])) {
                $pageParams['number'] = max(1, (int) $pageParams['number']);
            }

            if (isset($pageParams['size']) && is_numeric($pageParams['size'])) {
                $pageParams['size'] = max(1, (int) $pageParams['size']);
            }
        }

        if ($queryString !== '') {
            $pageSize = $this->queryValue($queryString, 'page[size]');
            if ($pageSize !== null && is_numeric($pageSize)) {
                $pageParams['size'] ??= max(1, (int) $pageSize);
            }

            $pageNumber = $this->queryValue($queryString, 'page[number]');
            if ($pageNumber !== null && is_numeric($pageNumber)) {
                $pageParams['number'] ??= max(1, (int) $pageNumber);
            }
        }

        if ($query->has('limit')) {
            $limit = $query->get('limit');

            if (is_numeric($limit)) {
                $limitInt = max(1, (int) $limit);

                $pageParams['size'] ??= $limitInt;
            }

            $query->remove('limit');
        }

        if (! empty($pageParams)) {
            $query->set('page', $pageParams);
        }

        return $next($request);
    }

    private function queryValue(string $queryString, string $key): ?string
    {
        $pattern = '/(?:^|&)'.preg_quote($key, '/').'=([^&]*)/';

        if (preg_match($pattern, $queryString, $matches) !== 1) {
            return null;
        }

        return urldecode($matches[1]);
    }
}
