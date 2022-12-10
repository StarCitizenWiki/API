<?php

declare(strict_types=1);

namespace App\Events;

use Dingo\Api\Http\Request;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiRouteCalled
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Request $request;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
