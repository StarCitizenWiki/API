<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Class AdminController
 *
 * @package App\Http\Controllers\Auth
 */
class AdminController extends Controller
{
    /**
     * Returns the View to list all routes
     *
     * @return View
     */
    public function showRoutesView() : View
    {
        return view('admin.routes.index');
    }
}
