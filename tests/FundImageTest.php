<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FundImageTest extends TestCase
{
    private $_gdInstalled = false;

    public function setUp()
    {
        parent::setUp();
        if (in_array('gd', get_loaded_extensions())) {
            $this->_gdInstalled = true;
        }
    }

    public function testFundImage()
    {
        if ($this->_gdInstalled) {
            $this->get('/media/images/funds');
            $this->assertResponseOk();
        }
    }

    public function testFundImageWithText()
    {
        if ($this->_gdInstalled) {
            $this->get('/media/images/funds/text');
            $this->assertResponseOk();
        }
    }

    public function testFundImageWithBars()
    {
        if ($this->_gdInstalled) {
            $this->get('/media/images/funds/bar');
            $this->assertResponseOk();
        }
    }
}
