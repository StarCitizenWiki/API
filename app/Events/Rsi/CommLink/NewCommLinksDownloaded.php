<?php

declare(strict_types=1);

namespace App\Events\Rsi\CommLink;

use App\Models\Rsi\CommLink\CommLinksChanged as CommLinkChangedModel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewCommLinksDownloaded
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @var Collection
     */
    public $commLinks;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        $this->commLinks = CommLinkChangedModel::query()->where('type', 'creation')->get();

        CommLinkChangedModel::query()->where('type', 'creation')->delete();
    }
}
