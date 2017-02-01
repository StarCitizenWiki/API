<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KopfbildToolController extends Controller
{
    public function index() {
        $kopfbildSettings = [
            'default' => [
                'outputWidth' => 1920,
                'outputHeight' => 250,
                'displayWidth' => 960,
                'displayHeight' => 125,
                'selectionRectangleColor' => '#ff0000'
            ]
        ];

        $bootstrapModules = [
            'enableCSS' =>  true,
            'enableJS' => false,
        ];

        return view('kopfbildtool.index', compact('kopfbildSettings'), compact('bootstrapModules'));
    }
}
