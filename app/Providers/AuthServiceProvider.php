<?php declare(strict_types = 1);

namespace App\Providers;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
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
        Gate::resource('web.admin.translations', 'App\Policies\Web\Admin\TranslationPolicy');

        /**
         * Internals = Datenbank IDs, etc.
         */
        Gate::define('web.admin.internals.view', function (Admin $admin) {
            return $admin->getHighestPermissionLevel() >= AdminGroup::SYSOP;
        });

        Gate::resource('web.admin.notifications', 'App\Policies\Web\Admin\Notification\NotificationPolicy');
        Gate::resource('web.admin.users', 'App\Policies\Web\Admin\User\UserPolicy');

        /**
         * Star Citizen
         */
        Gate::resource('web.admin.starcitizen.manufacturers', 'App\Policies\Web\Admin\StarCitizen\Manufacturer\ManufacturerPolicy');
        Gate::resource('web.admin.starcitizen.vehicles', 'App\Policies\Web\Admin\StarCitizen\Vehicle\VehiclePolicy');
    }
}
