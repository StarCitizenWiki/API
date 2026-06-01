<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Tools;

use App\Http\Controllers\Controller;
use App\Support\Starmap\SystemOrder;
use Illuminate\Contracts\View\View;

class RoutePlannerController extends Controller
{
    public function __invoke(): View
    {
        return view('tools.route-planner', [
            'systemOrder' => SystemOrder::SYSTEMS,
        ]);
    }
}
