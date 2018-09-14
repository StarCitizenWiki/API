<?php declare(strict_types = 1);

namespace App\Providers;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use App\Policies\Web\Admin\Notification\NotificationPolicy;
use App\Policies\Web\Admin\Rsi\CommLink\CommLinkPolicy;
use App\Policies\Web\Admin\StarCitizen\Manufacturer\ManufacturerPolicy;
use App\Policies\Web\Admin\StarCitizen\Vehicle\VehiclePolicy;
use App\Policies\Web\Admin\TranslationPolicy;
use App\Policies\Web\Admin\User\UserPolicy;
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
        Gate::define('web.admin.dashboard.view', 'App\Policies\Web\Admin\AdminPolicy@viewDashboard');
        Gate::define('web.admin.accept_license', 'App\Policies\Web\Admin\AdminPolicy@acceptLicense');
        Gate::resource('web.admin.translations', TranslationPolicy::class);

        /**
         * Internals = Datenbank IDs, etc.
         */
        Gate::define(
            'web.admin.internals.view',
            function (Admin $admin) {
                return $admin->getHighestPermissionLevel() >= AdminGroup::SYSOP;
            }
        );

        Gate::resource('web.admin.notifications', NotificationPolicy::class);
        Gate::resource('web.admin.users', UserPolicy::class);

        /**
         * Star Citizen
         */
        Gate::resource('web.admin.starcitizen.manufacturers', ManufacturerPolicy::class);
        Gate::resource('web.admin.starcitizen.vehicles', VehiclePolicy::class);

        /**
         * RSI
         */
        Gate::resource('web.admin.rsi.comm_links', CommLinkPolicy::class);
        Gate::define('web.admin.rsi.comm_links.update_settings', 'App\Policies\Web\Admin\Rsi\CommLink\CommLinkPolicy@updateSettings');
        Gate::define('web.admin.rsi.comm_links.preview', 'App\Policies\Web\Admin\Rsi\CommLink\CommLinkPolicy@preview');
    }
}
