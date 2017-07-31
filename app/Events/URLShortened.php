<?php declare(strict_types = 1);

namespace App\Events;

use App\Models\ShortURL\ShortURL;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class URLShortened
 *
 * @package App\Events
 */
class URLShortened implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $url;

    /**
     * Create a new event instance.
     *
     * @param ShortURL $url The generated ShortURL
     */
    public function __construct(ShortURL $url)
    {
        $this->url = $url;
    }
}
