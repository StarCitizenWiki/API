<?php declare(strict_types = 1);

namespace App\Events\Rsi\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommLinkChanged
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @var \App\Models\Rsi\CommLink\CommLink
     */
    public $commLink;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     */
    public function __construct(CommLink $commLink)
    {
        $this->commLink = $commLink;
    }
}
