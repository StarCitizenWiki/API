<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\CopyTranslationData;
use App\Console\Commands\FixChangelogNamespaces;
use App\Console\Commands\PopulateData;
use App\Console\Commands\Rsi\CommLink\CommLinkSchedule;
use App\Console\Commands\Rsi\CommLink\Download\DownloadCommLink;
use App\Console\Commands\Rsi\CommLink\Download\DownloadCommLinks;
use App\Console\Commands\Rsi\CommLink\Download\Image\DownloadCommLinkImages;
use App\Console\Commands\Rsi\CommLink\Download\ReDownloadCommLinks;
use App\Console\Commands\Rsi\CommLink\Image\CreateImageHashes;
use App\Console\Commands\Rsi\CommLink\Image\CreateImageMetadata;
use App\Console\Commands\Rsi\CommLink\Image\SyncImageIds;
use App\Console\Commands\Rsi\CommLink\Import\ImportCommLink;
use App\Console\Commands\Rsi\CommLink\Import\ImportCommLinks;
use App\Console\Commands\Rsi\CommLink\Translate\TranslateCommLinks;
use App\Console\Commands\Rsi\CommLink\Wiki\CreateCommLinkWikiPages;
use App\Console\Commands\Rsi\CommLink\Wiki\CreateCommLinkWikiTranslationPages;
use App\Console\Commands\StarCitizen\Galactapedia\ImportArticleProperties;
use App\Console\Commands\StarCitizen\Galactapedia\ImportArticles;
use App\Console\Commands\StarCitizen\Galactapedia\ImportCategories;
use App\Console\Commands\StarCitizen\Galactapedia\TranslateArticles;
use App\Console\Commands\StarCitizen\Galactapedia\Wiki\ApproveArticles;
use App\Console\Commands\StarCitizen\Galactapedia\Wiki\CreateWikiPages;
use App\Console\Commands\StarCitizen\Galactapedia\Wiki\UploadImages;
use App\Console\Commands\StarCitizen\ShipMatrix\Download\DownloadShipMatrix;
use App\Console\Commands\StarCitizen\ShipMatrix\Import\ImportShipMatrix;
use App\Console\Commands\StarCitizen\Starmap\Download\DownloadStarmap;
use App\Console\Commands\StarCitizen\Starmap\Import\ImportStarmap;
use App\Console\Commands\StarCitizen\Starmap\Translate\TranslateSystems;
use App\Console\Commands\StarCitizen\Stat\Download\DownloadStats;
use App\Console\Commands\StarCitizen\Stat\Import\ImportStats;
use App\Console\Commands\StarCitizen\Vehicle\ImportLoaner;
use App\Console\Commands\StarCitizen\Vehicle\ImportMsrp;
use App\Console\Commands\SC\ImportClothing;
use App\Console\Commands\SC\ImportItems;
use App\Console\Commands\SC\ImportPersonalWeapons;
use App\Console\Commands\SC\ImportShops;
use App\Console\Commands\SC\ImportVehicleItems;
use App\Console\Commands\SC\ImportVehicles;
use App\Console\Commands\SC\TranslateItems;
use App\Console\Commands\SC\Wiki\CreateCharArmorWikiPages;
use App\Console\Commands\SC\Wiki\CreateClothingWikiPages;
use App\Console\Commands\SC\Wiki\CreateCommodityWikiPages;
use App\Console\Commands\SC\Wiki\CreateFoodWikiPages;
use App\Console\Commands\SC\Wiki\CreateShipItemWikiPages;
use App\Console\Commands\SC\Wiki\CreateWeaponAttachmentWikiPages;
use App\Console\Commands\SC\Wiki\CreateWeaponWikiPages;
use App\Console\Commands\SC\Wiki\UploadItemImages;
use App\Console\Commands\Transcript\ImportMetadata;
use App\Console\Commands\Transcript\TranslateTranscripts;
use App\Events\Rsi\CommLink\CommLinksChanged as CommLinksChangedEvent;
use App\Events\Rsi\CommLink\NewCommLinksDownloaded;
use App\Jobs\Wiki\CommLink\UpdateCommLinkProofReadStatus;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel.
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        DownloadShipMatrix::class,
        ImportShipMatrix::class,

        ImportMsrp::class,
        ImportLoaner::class,

        DownloadStats::class,
        ImportStats::class,

        ImportCommLinks::class,
        ImportCommLink::class,
        CommLinkSchedule::class,

        DownloadCommLink::class,
        DownloadCommLinks::class,
        ReDownloadCommLinks::class,
        DownloadCommLinkImages::class,

        TranslateCommLinks::class,

        CreateCommLinkWikiPages::class,
        CreateCommLinkWikiTranslationPages::class,
        CreateWikiPages::class,

        SyncImageIds::class,
        CreateImageHashes::class,
        CreateImageMetadata::class,

        TranslateTranscripts::class,
        ImportMetadata::class,

        DownloadStarmap::class,
        ImportStarmap::class,
        TranslateSystems::class,

        ImportCategories::class,
        ImportArticles::class,
        ImportArticleProperties::class,
        TranslateArticles::class,
        ApproveArticles::class,
        UploadImages::class,

        FixChangelogNamespaces::class,

        ImportItems::class,
        ImportVehicles::class,
        ImportShops::class,
        ImportClothing::class,
        ImportVehicleItems::class,
        ImportPersonalWeapons::class,

        TranslateItems::class,

        CreateCommodityWikiPages::class,
        CreateCharArmorWikiPages::class,
        CreateClothingWikiPages::class,
        CreateShipItemWikiPages::class,
        CreateWeaponAttachmentWikiPages::class,
        CreateWeaponWikiPages::class,
        CreateFoodWikiPages::class,
        UploadItemImages::class,


        PopulateData::class,

        CopyTranslationData::class,
    ];

    /**
     * @var Schedule
     */
    private Schedule $schedule;

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $this->schedule = $schedule;

        $this->scheduleStatJobs();

        if (config('schedule.ship_matrix.enabled')) {
            $this->scheduleVehicleJobs();
        }

        if (config('schedule.comm_links.enabled')) {
            $this->scheduleCommLinkJobs();
        }

        if (config('schedule.starmap.enabled')) {
            $this->scheduleStarmapJobs();
        }

        if (config('schedule.galactapedia.enabled')) {
            $this->scheduleGalactapediaJobs();
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        require base_path('routes/console.php');
    }

    /**
     * Stat related Jobs.
     */
    private function scheduleStatJobs(): void
    {
        $this->schedule
            ->command(DownloadStats::class, ['--import'])
            ->dailyAt('20:00');
    }

    /**
     * Comm-Link related Jobs.
     */
    private function scheduleCommLinkJobs(): void
    {
        /* Check for new Comm-Links */
        $this->schedule
            ->command(CommLinkSchedule::class)
            ->everyFifteenMinutes();

        /* Run CommLink Notification only once each day */
        $this->schedule->call(function () {
            NewCommLinksDownloaded::dispatch();
        })->dailyAt('18:00');

        /* Re-Download all Comm-Links monthly */
        $this->schedule
            ->command(ReDownloadCommLinks::class, ['--skip=false'])
            ->monthly()
            ->after(
                function () {
                    CommLinksChangedEvent::dispatch();
                }
            );

        /* Download Comm-Link Images */
        if (config('schedule.comm_links.download_local') === true) {
            $this->schedule->job(DownloadCommLinkImages::class)->daily()->withoutOverlapping();
        }

        /* Update Proof Read Status */
        $this->schedule
            ->job(UpdateCommLinkProofReadStatus::class)
            ->daily()
            ->withoutOverlapping();
    }

    /**
     * Ship Matrix related Jobs.
     */
    private function scheduleVehicleJobs(): void
    {
        $hours = config('schedule.ship_matrix.at', []);
        // Ensure first and second key exists
        $hours = array_merge([1, 13], $hours);

        $this->schedule
            ->command(DownloadShipMatrix::class, ['--import'])
            ->twiceDaily(
                $hours[0],
                $hours[1],
            );

        $this->schedule
            ->command(ImportMsrp::class)
            ->daily();

        $this->schedule
            ->command(ImportLoaner::class)
            ->daily();
    }

    /**
     * Starmap download and import job
     */
    private function scheduleStarmapJobs(): void
    {
        $this->schedule
            ->command(DownloadStarmap::class, ['--import'])
            ->monthly();
    }

    /**
     * Galactapedia Jobs
     */
    private function scheduleGalactapediaJobs(): void
    {
        $this->schedule
            ->command(ImportCategories::class)
            ->onSuccess(function () {
                $this->call(ImportArticles::class);
            })
            ->dailyAt('2:00')
            ->withoutOverlapping();

        $this->schedule
            ->command(ImportArticleProperties::class)
            ->dailyAt('2:30')
            ->withoutOverlapping();

        $this->schedule
            ->command(TranslateArticles::class)
            ->dailyAt('3:00')
            ->withoutOverlapping();

        if (config('schedule.galactapedia.create_wiki_pages')) {
            $this->schedule
                ->command(CreateWikiPages::class)
                ->dailyAt('3:30')
                ->withoutOverlapping();
        }
    }
}
