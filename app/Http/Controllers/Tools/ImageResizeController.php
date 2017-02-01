<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImageResizeController extends Controller
{
    public function index() {
        $imageResizeSettings = [
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

        return view('tools.imageresizer', compact('imageResizeSettings'), compact('bootstrapModules'));
    }
}
