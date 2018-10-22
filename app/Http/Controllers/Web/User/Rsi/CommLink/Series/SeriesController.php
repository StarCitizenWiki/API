<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink\Series;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\Series\Series;

/**
 * Comm-Link Series
 */
class SeriesController extends Controller
{
    /**
     * CommLinkController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * All Series
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.user.rsi.comm-links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'user.rsi.comm_links.series.index',
            [
                'series' => Series::query()->orderBy('name')->get(),
            ]
        );
    }

    /**
     * Get all Comm-Links in a given Series
     *
     * @param \App\Models\Rsi\CommLink\Series\Series $series
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Series $series)
    {
        $this->authorize('web.user.rsi.comm-links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $links = $series->commLinks()->orderByDesc('cig_id')->paginate(20);

        return view(
            'user.rsi.comm_links.index',
            [
                'commLinks' => $links,
            ]
        );
    }
}
