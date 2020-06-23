<?php declare(strict_types = 1);

namespace App\Providers;

use App\Events\ModelUpdating;
use App\Events\Rsi\CommLink\CommLinksChanged;
use App\Events\Rsi\CommLink\NewCommLinksDownloaded;
use App\Listeners\Rsi\CommLink\SendCommLinksChangedNotification;
use App\Listeners\Rsi\CommLink\SendNewCommLinksDownloadedNotification;
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
        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\LogSuccessfulLogin',
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
    ];
}
