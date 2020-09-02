<?php

namespace App\Http\Controllers\Web\User\Rsi\Stat;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Stat\Stat;
use Illuminate\Http\Request;

class StatController extends Controller
{
    public function index() {
        $this->authorize('web.user.rsi.stats.view');

        $data = Stat::query()->get();

        return view(
            'user.rsi.stats.index',
            [
                'labels' => $data->pluck('created_at')->toJson(),
                'funds' => $data->pluck('funds')->toJson(),
                'fleet' => $data->pluck('fleet')->toJson(),
                'fans' => $data->pluck('fans')->toJson(),
            ]
        );
    }
}
