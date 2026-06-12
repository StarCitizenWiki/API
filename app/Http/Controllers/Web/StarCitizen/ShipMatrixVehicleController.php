<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\StarCitizen;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

#[CacheTag('vehicles')]
class ShipMatrixVehicleController extends Controller
{
    public function index(Request $request): View
    {
        return view('ship-matrix.index', [
            'initialHeaderFilter' => [],
        ]);
    }
}
