<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 26.07.2018
 * Time: 12:52
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;

/**
 * Base Controller that has Dingo Helpers
 */
abstract class AbstractApiController extends Controller
{
    use Helpers;
}
