<?php declare(strict_types = 1);

namespace Tests\Feature\Controller;

use Tests\TestCase;

/**
 * Class ShortUrlTest
 * @package Tests\Feature
 */
class ShortUrlWebControllerTest extends TestCase
{
    /**
     * @covers \App\Http\Controllers\ShortUrl\ShortUrlController::showResolveView()
     */
    public function testShortUrlResolveView()
    {
        $response = $this->get('resolve');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\ShortUrl\ShortUrlController::resolveAndDisplay()
     */
    public function testShortUrlResolveRedirect()
    {
        $response = $this->post('resolve', [
            'url' => 'https://localhost/'.str_random(6),
        ]);
        $response->assertStatus(302);
    }

    /**
     * Tests json resolve
     *
     * @covers \App\Http\Controllers\ShortUrl\ShortUrlController::resolve()
     * @covers \App\Transformers\ShortUrl\ShortUrlTransformer
     */
    public function testApiResolve()
    {
        $response = $this->post('api/v1/resolve', ['hash' => str_random(5)]);
        $response->assertSee('[]');
        $response->assertStatus(200);
    }

    /**
     * Test Repository Creation
     *
     * @covers \App\Http\Controllers\ShortUrl\ShortUrlController::create()
     * @covers \App\Transformers\ShortUrl\ShortUrlTransformer
     * @covers \App\Events\UrlShortened
     */
    public function testShortUrlCreationApi()
    {
        $response = $this->post('api/v1/shorten', [
            'url' => 'https://star-citizen.wiki/'.str_random(5),
            'hash' => str_random(6),
            'expires' => null,
        ]);

        $response->assertSee('original_url');
        $response->assertStatus(200);
    }

    /**
     * Test Repository Creation
     *
     * @covers \App\Http\Controllers\ShortUrl\ShortUrlController::createAndRedirect()
     */
    public function testShortUrlCreationView()
    {
        $response = $this->post('shorten', [
            'url' => 'https://star-citizen.wiki/'.str_random(5),
            'hash' => str_random(6),
            'expires' => null,
        ]);

        $response->assertStatus(302);
    }
}
