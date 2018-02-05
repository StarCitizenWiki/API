<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin;

use App\Models\ShortUrl\ShortUrl;
use App\Models\ShortUrl\ShortUrlWhitelist;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class ShortUrlControllerTest
 * @package Tests\Feature\Controller\Admin
 */
class ShortUrlControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $user;

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->user = User::find(1);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::showUrlsListView()
     */
    public function testUrlsView()
    {
        $response = $this->actingAs($this->user)->get('admin/urls');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::showUrlWhitelistView()
     */
    public function testUrlsWhitelistView()
    {
        $response = $this->actingAs($this->user)->get('admin/urls/whitelist');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::showAddUrlWhitelistView()
     */
    public function testAddUrlsWhitelistView()
    {
        $response = $this->actingAs($this->user)->get('admin/urls/whitelist/add');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::showEditUrlView()
     */
    public function testEditUrlView()
    {
        $url = ShortUrl::create([
            'url' => 'https://star-citizen.wiki/'.str_random(6),
            'hash' => str_random(5),
            'user_id' => 1,
        ]);
        $response = $this->actingAs($this->user)->get('admin/urls/'.$url->id);
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::showEditUrlView()
     */
    public function testEditUrlViewException()
    {
        $response = $this->actingAs($this->user)->get('admin/urls/-1');
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::deleteUrl()
     */
    public function testDeleteUrl()
    {
        $url = ShortUrl::create([
            'url' => 'https://star-citizen.wiki/'.str_random(6),
            'hash' => str_random(5),
            'user_id' => 1,
        ]);
        $response = $this->actingAs($this->user)->delete('admin/urls', [
            'id' => $url->id,
        ]);
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::deleteUrl()
     */
    public function testDeleteUrlException()
    {
        $response = $this->actingAs($this->user)->delete('admin/urls', [
            'id' => 999,
        ]);
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::deleteWhitelistUrl()
     */
    public function testDeleteWhitelistUrl()
    {
        $url = ShortUrlWhitelist::all()->first();
        $response = $this->actingAs($this->user)->delete('admin/urls/whitelist', [
            'id' => $url->id,
        ]);
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::deleteWhitelistUrl()
     */
    public function testDeleteWhitelistUrlException()
    {
        $response = $this->actingAs($this->user)->delete('admin/urls/whitelist', [
            'id' => 999,
        ]);
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::addWhitelistUrl()
     */
    public function testAddWhitelistUrl()
    {
        $response = $this->actingAs($this->user)->post('admin/urls/whitelist', [
            'url' => 'https://url.com',
            'internal' => false,
        ]);
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\ShortUrlController::updateUrl()
     * @covers \App\Models\ShortUrl\ShortUrl::updateShortUrl()
     */
    public function testUpdateUrl()
    {
        $url = ShortUrl::create([
            'url' => 'https://star-citizen.wiki/'.str_random(6),
            'hash' => str_random(5),
            'user_id' => 1,
        ]);
        $response = $this->actingAs($this->user)->patch('admin/urls', [
            'id' => $url->id,
            'url' => 'https://url.com',
            'hash' => str_random(5),
            'user_id' => 1,
        ]);
        $response->assertStatus(302);
    }
}