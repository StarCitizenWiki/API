<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Api;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Contracts\View\View;

/**
 * Class APIPageController
 */
class PageController extends Controller
{
    /**
     * Returns the API Index View
     *
     * @return View
     */
    public function index(): View
    {
        return view('api.pages.index');
    }

    /**
     * Returns the API FAQ View
     *
     * @return View
     */
    public function showFaqView(): View
    {
        return view('api.pages.faq');
    }
}
