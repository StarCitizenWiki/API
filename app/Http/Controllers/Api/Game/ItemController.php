<?php

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\Game\Item\ItemLinkCollection;
use App\Http\Resources\Game\Item\ItemResource;
use App\Models\Game\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $versionCode = $request->attributes->get('game_version_code');
        $items = Item::withDataForVersion($versionCode)->paginate();

        return new ItemLinkCollection($items);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Item $item)
    {
        $versionCode = $request->attributes->get('game_version_code');
        $item->load([
            'data' => fn ($query) => $query->forRequestedOrDefaultVersion($versionCode),
        ]);

        return new ItemResource($item);
    }
}
