<?php declare(strict_types = 1);

namespace App\Http\Controllers\Tool;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Class ImageResizeController
 */
class ImageResizeController extends Controller
{
    /**
     * Returns the Image Resize View
     *
     * @return \Illuminate\Contracts\View\View
     *
     * @throws \App\Exceptions\WrongMethodNameException
     */
    public function showImageResizeView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));
        $imageResizeSettings = [
            'default' => [
                'outputWidth' => 1920,
                'outputHeight' => 250,
                'displayWidth' => 960,
                'displayHeight' => 125,
                'selectionRectangleColor' => '#ff0000',
            ],
        ];

        return view('tools.imageresizer', compact('imageResizeSettings'));
    }
}
