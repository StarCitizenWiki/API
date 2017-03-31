<?php

namespace Tests\Feature;

use App\Exceptions\URLNotWhitelistedException;
use App\Http\Controllers\ShortURL\ShortURLController;
use App\Models\ShortURL\ShortURL;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Class ShortURLTest
 * @package Tests\Feature
 */
class ShortURLControllerTest extends TestCase
{
    /**
     * @covers ShortURLController::showResolveView()
     */
    public function testShortURLResolveView()
    {
        $response = $this->get('resolve');
        $response->assertStatus(200);
    }

    /**
     * @covers ShortURLController::resolveAndDisplay()
     */
    public function testShortURLResolveRedirect()
    {
        $response = $this->post('resolve', ['url' => config('app.shorturl_url').'/'.str_random(6)]);
        $response->assertStatus(302);
    }

    /**
     * Tests json resolve
     *
     * @covers ShortURLController::resolve()
     */
    public function testAPIResolve()
    {
        $response = $this->post('api/v1/resolve', ['hash_name' => str_random(5)]);
        $response->assertSee('[]');
        $response->assertStatus(200);
    }

    /**
     * Test Repository Creation
     *
     * @covers ShortURLController::create()
     */
    public function testShortURLCreationAPI()
    {
        $response = $this->post('api/v1/shorten', [
            'url' => 'https://star-citizen.wiki/'.str_random(5),
            'hash_name' => str_random(6),
        ]);

        $response->assertSee('original_url');
        $response->assertStatus(200);
    }

    /**
     * Test Repository Creation
     *
     * @covers ShortURLController::createAndRedirect()
     */
    public function testShortURLCreationView()
    {
        $response = $this->post('shorten', [
            'url' => 'https://star-citizen.wiki/'.str_random(5),
            'hash_name' => str_random(6),
        ]);

        $response->assertStatus(302);
    }
}
