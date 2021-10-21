<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizenUnpacked\Item;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{

    /**
     * @return View
     */
    public function index(): View
    {
        return view('user.starcitizenunpacked.item.index')->with('apiToken', optional(Auth::user())->api_token);
    }
}
