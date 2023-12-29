<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\StarCitizenUnpacked\Item;

use App\Http\Controllers\Controller;
use App\Models\SC\Item\Item;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    /**
     * An In-Game Item
     *
     * @param string $item
     *
     * @return View
     */
    public function show(string $item): View
    {
        return view(
            'web.starcitizenunpacked.item.show',
            [
                'item' => Item::query()->where('uuid', $item)->firstOrFail(),
            ]
        );
    }

    /**
     * @return View
     */
    public function index(): View
    {
        return view('web.starcitizenunpacked.item.index')->with('apiToken', optional(Auth::user())->api_token);
    }
}
