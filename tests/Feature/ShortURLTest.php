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

class ShortURLTest extends TestCase
{
    use WithoutMiddleware;

    public function testNotWhitelistedException()
    {
        $this->expectException(URLNotWhitelistedException::class);
        ShortURL::createShortURL(['url' => 'https://notwhitelisted.com']);
    }

    public function testHashNotExistsException()
    {
        $this->expectException(ModelNotFoundException::class);
        ShortURL::resolve('Does_Not_Exist');
    }

    public function testShortURLCreation()
    {
        $hash_name = str_random(6);
        $url = ShortURL::createShortURL([
            'url' => 'https://star-citizen.wiki/'.str_random(16),
            'hash_name' => $hash_name,
            'user_id' => 1
        ]);
        $this->assertEquals($hash_name, $url->hash_name);
    }
}