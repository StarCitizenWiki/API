<?php declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\Stat;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Stat\Stat;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatController extends Controller
{
    /**
     * @param Request $request
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function index(Request $request): View
    {
        $this->authorize('web.user.rsi.stats.view');

        $every = $request->get('skip', 100);

        if (!is_numeric($every) || $every < 0) {
            $every = 100;
        }

        if ($request->has('from') && !$request->has('skip')) {
            $every = 0;
        }

        $every = (int) $every;

        if ($every === 0) {
            $data = Stat::query();
        } else {
            $data = Stat::query()->whereRaw('id mod '.$every.' = 0');
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
                'fleet' => $data->pluck('fleet')->toJson(),
                'fans' => $data->pluck('fans')->toJson(),
                'active' => $every,
                'from' => optional($from)->format('Y-m-d'),
            ]
        );
    }
}
