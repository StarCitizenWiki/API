<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 04.09.2017
 * Time: 12:32
 */

namespace App\Traits;

/**
 * Trait ChecksIfRequestWantsJsonTrait
 */
trait ChecksIfRequestWantsJsonTrait
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function wantsJson($request): bool
    {
        return $request->wantsJson() || $request->query('format', null) === 'json';
    }
}
