<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Api;

use App\Http\Controllers\Controller;
use App\Models\SC\Item\Item;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class ItemController extends Controller
{
    /**
     * An In-Game Item
     *
     * @param Item $item
     * @return Application|Factory|View
     */
    public function show(string $item)
    {
        return view(
            'api.pages.items.show',
            [
                'item' => Item::query()->where('uuid', $item)->firstOrFail(),
            ]
        );
    }
}
