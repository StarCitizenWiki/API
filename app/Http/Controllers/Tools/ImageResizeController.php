<?php declare(strict_types = 1);

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Traits\ProfilesMethodsTrait;
use Illuminate\View\View;

/**
 * Class ImageResizeController
 *
 * @package App\Http\Controllers\Tools
 */
class ImageResizeController extends Controller
{
    use ProfilesMethodsTrait;

    /**
     * Returns the Image Resize View
     *
     * @return View
     */
    public function showImageResizeView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));
        $imageResizeSettings = [
            'default' => [
                'outputWidth'             => 1920,
                'outputHeight'            => 250,
                'displayWidth'            => 960,
                'displayHeight'           => 125,
                'selectionRectangleColor' => '#ff0000',
            ],
        ];

        return view('tools.imageresizer', compact('imageResizeSettings'));
    }
}
