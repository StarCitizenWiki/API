<?php declare(strict_types = 1);

namespace Tests\Feature\Controller;

use Tests\TestCase;

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
