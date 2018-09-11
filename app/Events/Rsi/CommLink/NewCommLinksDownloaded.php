<?php declare(strict_types = 1);

namespace App\Events\Rsi\CommLink;

use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewCommLinksDownloaded
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    public $newCommLinks;

    /**
     * Create a new event instance.
     *
     * @param \Illuminate\Database\Eloquent\Collection $newCommLinks
     */
    public function __construct(Collection $newCommLinks)
    {
        $this->newCommLinks = $newCommLinks;
    }
}
