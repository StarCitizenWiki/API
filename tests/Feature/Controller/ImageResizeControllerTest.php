<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Class ImageResizeControllerTest
 * @package Tests\Feature\Controller
 */
class ImageResizeControllerTest extends TestCase
{
    /**
     * @covers \App\Http\Controllers\Tools\ImageResizeController::showImageResizeView()
     */
    public function testView()
    {
        $response = $this->get('tools/imageresizer');
        $response->assertSee('Star Citizen Wiki');
    }
}
