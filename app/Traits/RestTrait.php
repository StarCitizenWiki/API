<?php
/**
 * User: Hannes
 * Date: 21.03.2017
 * Time: 14:38
 */

namespace App\Traits;

use Illuminate\Http\Request;

/**
 * Class RestTrait
 *
 * @package App\Traits
 */
trait RestTrait
{
    /**
     * Determines if request is an api call.
     *
     * If the request URI contains '/api/v'.
     *
     * @param Request $request Request to check
     *
     * @return bool
     */
    protected function isApiCall(Request $request)
    {
        return str_contains($request->getUri(), '/api/v');
    }
}
