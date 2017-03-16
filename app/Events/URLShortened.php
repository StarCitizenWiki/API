<?php

namespace App\Events;

use App\Models\ShortURL\ShortURL;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class URLShortened
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $url;

    /**
     * Create a new event instance.
     *
     * @param ShortURL $url
     */
    public function __construct(ShortURL $url)
    {
        $this->url = $url;
    }
}
