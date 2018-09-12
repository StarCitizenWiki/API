<?php declare(strict_types = 1);

namespace App\Providers;

use App\Events\ModelUpdating;
use App\Events\Rsi\CommLink\CommLinkChanged;
use App\Events\Rsi\CommLink\NewCommLinksDownloaded;
use App\Listeners\Rsi\CommLink\SendCommLinkChangedNotification;
use App\Listeners\Rsi\CommLink\SendNewCommLinksNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\MediaWiki\MediaWikiExtendSocialite;

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
        'Illuminate\Auth\Events\Registered' => [
            'App\Listeners\SendUserWelcomeMail',
        ],
        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\LogSuccessfulLogin',
        ],
        ModelUpdating::class => [
            \App\Listeners\ModelUpdating::class,
        ],
        SocialiteWasCalled::class => [
            MediaWikiExtendSocialite::class,
        ],

        /**
         * Comm Links
         */
        NewCommLinksDownloaded::class => [
            SendNewCommLinksNotification::class,
        ],
        CommLinkChanged::class => [
            SendCommLinkChangedNotification::class,
        ],
    ];
}
