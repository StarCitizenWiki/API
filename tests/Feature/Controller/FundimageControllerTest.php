<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Class FundimageControllerTest
 * @package Tests\Feature\Controller
 */
class FundimageControllerTest extends TestCase
{
    /**
     * @covers \App\Http\Controllers\Tools\FundImageController::getImage()
     * @covers \App\Http\Controllers\Tools\FundImageController
     */
    public function testFundImage()
    {
        $response = $this->get('media/images/funds');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Tools\FundImageController::getImage()
     * @covers \App\Http\Controllers\Tools\FundImageController
     */
    public function testFundImageColor()
    {
        $response = $this->get('media/images/funds?color=ff0000');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Tools\FundImageController::getImageWithText()
     * @covers \App\Http\Controllers\Tools\FundImageController
     */
    public function testFundImageWithText()
    {
        $response = $this->get('media/images/funds/text');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Tools\FundImageController::getImageWithBars()
     * @covers \App\Http\Controllers\Tools\FundImageController
     */
    public function testFundImageWithBars()
    {
        $response = $this->get('media/images/funds/bar');
        $response->assertStatus(200);
    }
}
