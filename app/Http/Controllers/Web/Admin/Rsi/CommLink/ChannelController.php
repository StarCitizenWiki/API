<?php declare(strict_types = 1);

namespace App\Http\Controllers\Web\Admin\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\CommLink;

/**
 * Comm Link Channel Controller
 */
class ChannelController extends Controller
{
    /**
     * Get all Comm Links of a given Channel
     *
     * @param \App\Models\Rsi\CommLink\Channel\Channel $channel
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Channel $channel)
    {
        $links = CommLink::where('channel_id', $channel->id)->paginate(20);

        return view(
            'admin.rsi.comm_links.index',
            [
                'commLinks' => $links,
            ]
        );
    }
}
