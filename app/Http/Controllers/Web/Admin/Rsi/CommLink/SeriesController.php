<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Series\Series;

/**
 * Comm Link Series
 */
class SeriesController extends Controller
{
    /**
     * Get all Comm Links in a given Series
     *
     * @param \App\Models\Rsi\CommLink\Series\Series $series
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Series $series)
    {
        $links = CommLink::where('series_id', $series->id)->paginate(20);

        return view(
            'admin.rsi.comm_links.index',
            [
                'commLinks' => $links,
            ]
        );
    }
}
