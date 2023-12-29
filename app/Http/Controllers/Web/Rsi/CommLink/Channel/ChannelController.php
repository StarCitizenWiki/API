<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Rsi\CommLink\Channel;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\Channel\Channel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;

/**
 * Comm-Link Channel Controller
 */
class ChannelController extends Controller
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
     * All Channels
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function index(): View
    {
        $this->authorize('web.rsi.comm-links.view');

        return view(
            'web.rsi.comm_links.channels.index',
            [
                'channels' => Channel::query()->orderBy('name')->get(),
            ]
        );
    }

    /**
     * Get all Comm-Links of a given Channel
     *
     * @param Channel $channel
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function show(Channel $channel): View
    {
        $this->authorize('web.rsi.comm-links.view');

        $links = $channel->commLinks()->orderByDesc('cig_id')->paginate(20);

        return view(
            'web.rsi.comm_links.index',
            [
                'commLinks' => $links,
            ]
        );
    }
}
