<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Api;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class CommLinkController extends Controller
{
    /**
     * Shows a singular comm-link without needing to be authorized
     *
     * @param CommLink $commLink
     *
     * @return Application|Factory|View
     */
    public function show(CommLink $commLink)
    {
        return view(
            'api.pages.comm_links.show',
            [
                'commLink' => $commLink,
                'prev' => $commLink->getPrevAttribute(),
                'next' => $commLink->getNextAttribute(),
            ]
        );
    }
}
