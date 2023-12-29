<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\StarCitizenUnpacked;

use App\Http\Controllers\Controller;
use App\Models\SC\Vehicle\Vehicle;
use Illuminate\Contracts\View\View;

class VehicleController extends Controller
{
    /**
     * @return View
     */
    public function index(): View
    {
        return view('web.starcitizenunpacked.vehicle.index', [
            'vehicles' => Vehicle::query()->orderBy('is_ship')->get(),
        ]);
    }
}
