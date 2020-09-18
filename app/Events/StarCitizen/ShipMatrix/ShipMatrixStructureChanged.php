<?php declare(strict_types=1);

namespace App\Events\StarCitizen\ShipMatrix;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShipMatrixStructureChanged
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
}
