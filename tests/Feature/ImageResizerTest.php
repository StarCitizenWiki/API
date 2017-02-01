<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ImageResizerTest extends TestCase
{
    public function testView() : void
    {
        $this->get('/tools/imageresizer')
             ->assertSee('Star Citizen Wiki Kopfbild Generator');
    }
}
