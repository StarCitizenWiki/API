<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FundImageTest extends TestCase
{

    public function testImageGeneration()
    {
        $this->visit('/fundImage')->assertResponseOk();
    }
}
