<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Class APIPageController
 *
 * @package App\Http\Controllers
 */
class APIPageController extends Controller
{
    /**
     * Returns the API Index View
     *
     * @return View
     */
    public function showAPIView() : View
    {
        $this->logger::debug('API Index requested');

        return view('api.index');
    }

    /**
     * Returns the API FAQ View
     *
     * @return View
     */
    public function showFAQView() : View
    {
        $this->logger::debug('API FAQ requested');

        return view('api.faq');
    }
}
