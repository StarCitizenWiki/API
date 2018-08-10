<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 10.08.2018
 * Time: 12:00
 */

namespace Tests\Feature\Controller\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Providers\DingoTokenAuthProvider
 *
 * @covers \App\Http\Middleware\Api\UpdateTokenTimestamp
 * @covers \App\Http\Middleware\CheckUserState
 * @covers \App\Http\Middleware\PiwikTracking
 *
 * @covers \App\Http\Throttle\ApiThrottle
 */
class AbstractApiTestCase extends TestCase
{
    use RefreshDatabase;
}
