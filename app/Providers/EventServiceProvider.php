<?php declare(strict_types = 1);

namespace App\Providers;

use App\Events\ModelUpdating;
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
        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\LogSuccessfulLogin',
        ],
        ModelUpdating::class => [
            \App\Listeners\ModelUpdating::class,
        ],
    ];
}
