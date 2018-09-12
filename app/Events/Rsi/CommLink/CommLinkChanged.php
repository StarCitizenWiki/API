<?php declare(strict_types = 1);

namespace App\Events\Rsi\CommLink;

use App\Models\Rsi\CommLink\CommLinkChanged as CommLinkChangedModel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommLinkChanged
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    public $commLinks;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        $this->commLinks = CommLinkChangedModel::where('type', 'update')->get();
        CommLinkChangedModel::query()->where('type', 'update')->delete();
    }
}
