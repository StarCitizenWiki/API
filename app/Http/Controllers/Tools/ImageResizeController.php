<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImageResizeController extends Controller
{
    public function showImageResizeView() {
        $imageResizeSettings = [
            'default' => [
                'outputWidth' => 1920,
                'outputHeight' => 250,
                'displayWidth' => 960,
                'displayHeight' => 125,
                'selectionRectangleColor' => '#ff0000'
            ]
        ];

        return view('tools.imageresizer', compact('imageResizeSettings'));
    }
}
