<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class KopfbildtoolTest extends TestCase
{
    public function testView() : void
    {
        $this->visit('/kopfbildtool')
             ->see('Star Citizen Wiki Kopfbild Generator');
    }

    public function testDownload(): void
    {
        $this->visit('/kopfbildtool')
             ->attach(__DIR__ . '/resources/wiki_logo_Facebook.jpg', 'image')
             ->click('save')
             ->assertResponseStatus(200);
    }
}
