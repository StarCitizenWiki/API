<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Exceptions\HashNameAlreadyAssignedException;
use App\Exceptions\URLNotWhitelistedException;
use App\Http\Controllers\Controller;
use App\Models\ShortURL\ShortURL;
use App\Models\ShortURL\ShortURLWhitelist;
use App\Models\Starsystem;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

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
