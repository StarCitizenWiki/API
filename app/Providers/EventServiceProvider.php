<?php declare(strict_types = 1);

namespace App\Providers;

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
        'Illuminate\Auth\Events\Registered' => [
            'App\Listeners\SendUserWelcomeMail',
        ],
        'Illuminate\Auth\Events\Login'      => [
            'App\Listeners\LogSuccessfulLogin',
        ],
        'App\Events\UrlShortened'           => [
            'App\Listeners\SendUrlShortenedNotification',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        //
    }
}
