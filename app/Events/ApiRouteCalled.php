<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiRouteCalled
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public array $request;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $request)
    {
        $this->request = $request;
    }
}
