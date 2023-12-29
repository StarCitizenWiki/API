<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\Account\AccountPolicy;
use App\Policies\Web\Changelog\ChangelogPolicy;
use App\Policies\Web\DashboardPolicy;
use App\Policies\Web\Job\JobPolicy;
use App\Policies\Web\Rsi\CommLink\CommLinkPolicy;
use App\Policies\Web\Rsi\Stat\StatPolicy;
use App\Policies\Web\StarCitizen\Manufacturer\ManufacturerPolicy;
use App\Policies\Web\StarCitizen\Starmap\StarmapPolicy;
use App\Policies\Web\StarCitizen\Vehicle\VehiclePolicy;
use App\Policies\Web\Transcript\TranscriptPolicy;
use App\Policies\Web\TranslationPolicy;
use App\Policies\Web\User\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Class AuthServiceProvider.
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
    public function boot(): void
    {
        /*
         * Admin Gates
         */
        Gate::resource('web.dashboard', DashboardPolicy::class);
        Gate::resource('web.account', AccountPolicy::class);
        Gate::resource('web.translations', TranslationPolicy::class);
        Gate::resource('web.changelogs', ChangelogPolicy::class);
        Gate::resource(
            'web.jobs',
            JobPolicy::class,
            [
                'upload_csv' => 'uploadCsv',

                'start_translation' => 'startCommLinkTranslationJob',
                'start_wiki_page_creation' => 'startCommLinkWikiPageCreationJob',
                'start_image_download' => 'startCommLinkImageDownloadJob',
                'start_download' => 'startCommLinkDownloadJob',
                'start_proofread_update' => 'startCommLinkProofReadStatusUpdateJob',

                'start_ship_matrix_download' => 'startShipMatrixDownloadImportJob',
                'start_msrp_import' => 'startVehicleMsrpImportJob',

                'import_galactapedia_job' => 'startImportGalactapediaJob',

                'view_failed' => 'view',
                'truncate' => 'truncate',
            ]
        );

        /*
         * Internals = Datenbank IDs, etc.
         */
        Gate::define(
            'web.internals.view',
            static function (User $admin) {
                return $admin->getHighestPermissionLevel() >= UserGroup::SYSOP;
            }
        );

        Gate::resource('web.users', UserPolicy::class);

        /*
         * Star Citizen
         */
        Gate::resource('web.starcitizen.manufacturers', ManufacturerPolicy::class);
        Gate::resource('web.starcitizen.vehicles', VehiclePolicy::class);
        Gate::resource('web.starcitizen.starmap', StarmapPolicy::class);

        /*
         * RSI
         */
        Gate::resource('web.rsi.comm-links', CommLinkPolicy::class);
        Gate::define('web.rsi.comm-links.preview', 'App\Policies\Web\Rsi\CommLink\CommLinkPolicy@preview');

        Gate::resource('web.rsi.stats', StatPolicy::class);

        /*
         * SC
         */
        Gate::define(
            'web.jobs.sc-import',
            static function (User $admin) {
                return $admin->getHighestPermissionLevel() >= UserGroup::SYSOP;
            }
        );

        /*
         * Transcripts
         */
        Gate::resource(
            'web.transcripts',
            TranscriptPolicy::class,
            [
                'index' => 'index',
                'view' => 'view',
                'update' => 'update',
            ]
        );
    }
}
