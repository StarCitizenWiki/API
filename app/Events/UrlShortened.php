<?php declare(strict_types=1);

namespace App\Events;

use App\Models\ShortUrl\ShortUrl;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class UrlShortened
 */
class UrlShortened implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $url;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\ShortUrl\ShortUrl $url The generated ShortUrl
     */
    public function __construct(ShortUrl $url)
    {
        $this->url = $url;
    }
}
