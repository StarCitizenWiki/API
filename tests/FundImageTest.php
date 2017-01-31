<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FundImageTest extends TestCase
{

    public function testImageGeneration()
    {
        if (!in_array('gd', get_loaded_extensions())) {
            $this->expectException(\App\Exceptions\MissingExtensionException::class);
            $this->visit('/fundImage');
        } else {
            $this->visit('/fundImage');
            $this->assertResponseOk();
        }
    }
}
