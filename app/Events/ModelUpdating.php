<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class ModelUpdating
 */
class ModelUpdating
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @var Model The model updating its attributes
     */
    public Model $model;

    /**
     * Create a new event instance.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
