<?php declare(strict_types = 1);

namespace Tests\Feature\Controller;

use Tests\TestCase;

/**
 * Class FundimageControllerTest
 * @package Tests\Feature\Controller
 */
class FundimageControllerTest extends TestCase
{
    /**
     * @covers \App\Http\Controllers\Tool\FundImageController::getImage()
     * @covers \App\Http\Controllers\Tool\FundImageController
     */
    public function testFundImage()
    {
        $response = $this->get('media/images/funds');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Tool\FundImageController::getImage()
     * @covers \App\Http\Controllers\Tool\FundImageController
     */
    public function testFundImageColor()
    {
        $response = $this->get('media/images/funds?color=ff0000');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Tool\FundImageController::getImageWithText()
     * @covers \App\Http\Controllers\Tool\FundImageController
     */
    public function testFundImageWithText()
    {
        $response = $this->get('media/images/funds/text');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Tool\FundImageController::getImageWithBars()
     * @covers \App\Http\Controllers\Tool\FundImageController
     */
    public function testFundImageWithBars()
    {
        $response = $this->get('media/images/funds/bar');
        $response->assertStatus(200);
    }
}
