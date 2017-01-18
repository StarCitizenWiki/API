<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class KopfbildtoolTest extends TestCase
{
    public function testView() {
        $this->visit('/kopfbildtool')
             ->see('Star Citizen Wiki Kopfbild Generator');
    }
}
