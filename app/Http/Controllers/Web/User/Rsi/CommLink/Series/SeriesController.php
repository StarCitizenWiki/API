<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink\Series;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\Series\Series;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\View\View;

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
     * @return View
     *
     * @throws AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.user.rsi.comm-links.view');

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
     * @param Series $series
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function show(Series $series): View
    {
        $this->authorize('web.user.rsi.comm-links.view');

        return view(
            'user.rsi.comm_links.index',
            [
                'commLinks' => $series->commLinks()
                    ->orderByDesc('cig_id')
                    ->paginate(20),
            ]
        );
    }
}
