<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use App\Models\User;
use Illuminate\Contracts\View\View;

/**
 * Class AdminController
 *
 * @package App\Http\Controllers\Auth
 */
class AdminController extends Controller
{
    /**
     * Returns the Dashboard View
     *
     * @return View
     */
    public function showDashboardView() : View
    {
        return view('admin.dashboard')
            ->with('users', User::all())
            ->with('urls', ShortURL::all());
    }

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
