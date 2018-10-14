<?php declare(strict_types = 1);

namespace App\Providers;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\User\Account\AccountPolicy;
use App\Policies\Web\User\Changelog\ChangelogPolicy;
use App\Policies\Web\User\DashboardPolicy;
use App\Policies\Web\User\Notification\NotificationPolicy;
use App\Policies\Web\User\Rsi\CommLink\CommLinkPolicy;
use App\Policies\Web\User\Rsi\CommLink\Image\ImagePolicy;
use App\Policies\Web\User\StarCitizen\Manufacturer\ManufacturerPolicy;
use App\Policies\Web\User\StarCitizen\Vehicle\VehiclePolicy;
use App\Policies\Web\User\TranslationPolicy;
use App\Policies\Web\User\User\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Class AuthServiceProvider
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        /**
         * Admin Gates
         */
        Gate::resource('web.user.dashboard', DashboardPolicy::class);
        Gate::resource('web.user.account', AccountPolicy::class);
        Gate::resource('web.user.translations', TranslationPolicy::class);
        Gate::resource('web.user.changelogs', ChangelogPolicy::class);

        /**
         * Internals = Datenbank IDs, etc.
         */
        Gate::define(
            'web.user.internals.view',
            function (User $admin) {
                return $admin->getHighestPermissionLevel() >= UserGroup::SYSOP;
            }
        );

        Gate::resource('web.user.notifications', NotificationPolicy::class);
        Gate::resource('web.user.users', UserPolicy::class);

        /**
         * Star Citizen
         */
        Gate::resource('web.user.starcitizen.manufacturers', ManufacturerPolicy::class);
        Gate::resource('web.user.starcitizen.vehicles', VehiclePolicy::class);

        /**
         * RSI
         */
        Gate::resource('web.user.rsi.comm-links', CommLinkPolicy::class);
        Gate::define('web.user.rsi.comm-links.preview', 'App\Policies\Web\User\Rsi\CommLink\CommLinkPolicy@preview');

        Gate::resource('web.user.rsi.comm-links.images', ImagePolicy::class);
    }
}
