<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\Request;

trait MergesIncludeParam
{
    /**
     * Merge the request's include param with controller defaults into a
     * deduplicated, comma-separated include list.
     *
     * @param  array<int, string>  $defaults
     */
    private function mergeInclude(Request $request, array $defaults): string
    {
        $requested = explode(',', (string) $request->query('include', ''))
                |> (static fn ($x) => array_map('trim', $x))
                |> array_filter(...);

        return array_merge($requested, $defaults)
                |> array_unique(...)
                |> array_values(...)
                |> (static fn ($x) => implode(',', $x));
    }
}
