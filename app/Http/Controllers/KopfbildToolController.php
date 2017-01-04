<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KopfbildToolController extends Controller
{
    public function index() {
        $kopfbildSettings = [
            'default' => [
                'width' => 1920,
                'hight' => 250,
                'outputwidth' => 960,
                'outputhight' => 125
            ]
        ];

        $settings = [
            'bootstrap-css' => true,
            'bootstrap-js' => false,
        ];

        return view('kopfbildtool.index', compact('kopfbildSettings'), compact('settings'));
    }
}
