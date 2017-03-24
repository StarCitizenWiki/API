<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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
        return view('api.index');
    }
}
