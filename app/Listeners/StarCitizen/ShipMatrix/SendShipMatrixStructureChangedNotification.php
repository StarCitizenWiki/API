<?php declare(strict_types=1);

namespace App\Listeners\StarCitizen\ShipMatrix;

use App\Models\Account\User\User;
use App\Notifications\StarCitizen\ShipMatrix\ShipMatrixStructureChanged as ShipMatrixStructureChangedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class SendShipMatrixStructureChangedNotification
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(): void
    {
        /** @var Collection $admins */
        $admins = User::query()->whereNotNull('email')->whereHas('adminGroup')->get();

        Notification::send($admins, new ShipMatrixStructureChangedNotification());
    }
}
