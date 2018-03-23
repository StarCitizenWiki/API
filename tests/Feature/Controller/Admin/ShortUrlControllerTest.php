<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin;

use App\Models\Admin\Admin;
use App\Models\ShortUrl\ShortUrl;
use App\Models\ShortUrl\ShortUrlWhitelist;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class ShortUrlControllerTest
 * @package Tests\Feature\Controller\Admin
 */
class ShortUrlControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $admin;

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::showUrlsListView()
     */
    public function testUrlsView()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('admin/urls');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::showUrlWhitelistView()
     */
    public function testUrlsWhitelistView()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('admin/urls/whitelist');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::showAddUrlWhitelistView()
     */
    public function testAddUrlsWhitelistView()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('admin/urls/whitelist/add');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::showEditUrlView()
     */
    public function testEditUrlView()
    {
        $url = ShortUrl::create(
            [
                'url'     => 'https://star-citizen.wiki/'.str_random(6),
                'hash'    => str_random(5),
                'user_id' => 1,
            ]
        );
        $response = $this->actingAs($this->admin, 'admin')->get("admin/urls/{$url->getRouteKey()}");
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::showEditUrlView()
     */
    public function testEditUrlViewException()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('admin/urls/NotExistent');
        $response->assertStatus(400);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::deleteUrl()
     */
    public function testDeleteUrl()
    {
        $url = ShortUrl::create(
            [
                'url'     => 'https://star-citizen.wiki/'.str_random(6),
                'hash'    => str_random(5),
                'user_id' => 1,
            ]
        );
        $response = $this->actingAs($this->admin, 'admin')->delete("admin/urls/{$url->getRouteKey()}");
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::deleteUrl()
     */
    public function testDeleteUrlException()
    {
        $response = $this->actingAs($this->admin, 'admin')->delete('admin/urls/NotExistent');
        $response->assertStatus(400);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::deleteWhitelistUrl()
     */
    public function testDeleteWhitelistUrl()
    {
        $url = ShortUrlWhitelist::all()->first();
        $response = $this->actingAs($this->admin, 'admin')->delete("admin/urls/whitelist/{$url->getRouteKey()}");
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::deleteWhitelistUrl()
     */
    public function testDeleteWhitelistUrlException()
    {
        $response = $this->actingAs($this->admin, 'admin')->delete('admin/urls/whitelist/NotExistent');
        $response->assertStatus(400);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::addWhitelistUrl()
     */
    public function testAddWhitelistUrl()
    {
        $response = $this->actingAs($this->admin, 'admin')->post(
            'admin/urls/whitelist',
            [
                'url'      => 'https://url.com',
                'internal' => false,
            ]
        );
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\User\ShortUrlController::showAddUrlView()
     */
    public function testUrlAddView()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('admin/urls/add');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\User\ShortUrlController::addUrl()
     */
    public function testAddUrl()
    {
        $response = $this->actingAs($this->admin, 'admin')->post('admin/urls', [
            'url' => 'https://star-citizen.wiki/'.str_random(4),
            'hash' => str_random(4),
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('admin/urls');
    }

    /**
     * @covers \App\Http\Controllers\User\ShortUrlController::addUrl()
     */
    public function testAddUrlException()
    {
        $response = $this->actingAs($this->admin, 'admin')->post('admin/urls', [
            'url' => 'https://notwhitelisted.wiki/'.str_random(4),
            'hash' => str_random(4),
        ]);
        $response->assertStatus(302);
    }


    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::updateUrl()
     * @covers \App\Models\ShortUrl\ShortUrl::updateShortUrl()
     */
    public function testUpdateUrl()
    {
        $url = ShortUrl::create(
            [
                'url'     => 'https://star-citizen.wiki/'.str_random(6),
                'hash'    => str_random(5),
                'user_id' => 1,
            ]
        );
        $response = $this->actingAs($this->admin, 'admin')->patch(
            "admin/urls/{$url->getRouteKey()}",
            [
                'url'     => 'https://url.com',
                'hash'    => str_random(5),
                'user_id' => 1,
            ]
        );
        $response->assertStatus(302);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->admin = Admin::find(1);
    }
}
