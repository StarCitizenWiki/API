<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\Rsi\CommLink\Series;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\Series\Series;

/**
 * Comm Link Series
 */
class SeriesController extends Controller
{
    /**
     * All Series
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.admin.rsi.comm-links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'admin.rsi.comm_links.series.index',
            [
                'series' => Series::orderBy('name')->get(),
            ]
        );
    }

    /**
     * Get all Comm Links in a given Series
     *
     * @param \App\Models\Rsi\CommLink\Series\Series $series
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Series $series)
    {
        $this->authorize('web.admin.rsi.comm-links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $links = $series->commLinks()->orderByDesc('cig_id')->paginate(20);

        return view(
            'admin.rsi.comm_links.index',
            [
                'commLinks' => $links,
            ]
        );
    }
}
