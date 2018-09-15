<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Api;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\CommLink;

class CommLinkController extends Controller
{
    public function show(CommLink $commLink)
    {
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'api.comm_links.show',
            [
                'commLink' => $commLink,
            ]
        );
    }
}
