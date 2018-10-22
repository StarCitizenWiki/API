<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\User\Rsi\CommLink\Channel;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\Channel\Channel;

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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('web.user.rsi.comm-links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        return view(
            'user.rsi.comm_links.channels.index',
            [
                'channels' => Channel::query()->orderBy('name')->get(),
            ]
        );
    }

    /**
     * Get all Comm-Links of a given Channel
     *
     * @param \App\Models\Rsi\CommLink\Channel\Channel $channel
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Channel $channel)
    {
        $this->authorize('web.user.rsi.comm-links.view');
        app('Log')::debug(make_name_readable(__FUNCTION__));

        $links = $channel->commLinks()->orderByDesc('cig_id')->paginate(20);

        return view(
            'user.rsi.comm_links.index',
            [
                'commLinks' => $links,
            ]
        );
    }
}
