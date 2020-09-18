<?php declare(strict_types = 1);

namespace App\Providers;

use App\Events\ModelUpdating;
use App\Events\Rsi\CommLink\CommLinksChanged;
use App\Events\Rsi\CommLink\NewCommLinksDownloaded;
use App\Events\StarCitizen\ShipMatrix\ShipMatrixStructureChanged;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\Rsi\CommLink\SendCommLinksChangedNotification;
use App\Listeners\Rsi\CommLink\SendNewCommLinksDownloadedNotification;
use App\Listeners\StarCitizen\ShipMatrix\SendShipMatrixStructureChangedNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Login::class => [
            LogSuccessfulLogin::class,
        ],
        ModelUpdating::class => [
            \App\Listeners\ModelUpdating::class,
        ],

        /**
         * Comm-Links
         */
        NewCommLinksDownloaded::class => [
            SendNewCommLinksDownloadedNotification::class,
        ],
        CommLinksChanged::class => [
            SendCommLinksChangedNotification::class,
        ],

        ShipMatrixStructureChanged::class => [
            SendShipMatrixStructureChangedNotification::class,
        ],
    ];
}
