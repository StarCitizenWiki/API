<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\Stat;

use App\Http\Controllers\Controller;
use App\Models\StarCitizen\Stat\Stat;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StatController extends Controller
{
    /**
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $every = $request->get('skip', 100);

        if (!is_numeric($every) || $every < 0) {
            $every = 100;
        }

        if ($request->has('from') && !$request->has('skip')) {
            $every = 0;
        }

        $every = (int)$every;

        if ($every === 0 || config('database.default') === 'sqlite') {
            $data = Stat::query();
        } else {
            $data = Stat::query()->whereRaw('id mod ' . $every . ' = 0');
        }

        $from = null;

        if ($request->has('from')) {
            $from = $request->get('from');
            try {
                $from = Carbon::parse($from);
                $every = -1;
                $data->where('created_at', '>=', $from->format('Y-m-d'));
            } catch (InvalidFormatException $e) {
                //
            }
        }

        $data = $data->get();

        return view(
            'user.rsi.stats.index',
            [
                'labels' => $data->pluck('created_at')->toJson(),
                'funds' => $data->pluck('funds')->toJson(),
                'fans' => $data->pluck('fans')->toJson(),
                'active' => $every,
                'from' => optional($from)->format('Y-m-d'),
            ]
        );
    }
}
