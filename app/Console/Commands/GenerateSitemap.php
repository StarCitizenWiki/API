<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Game\Blueprint;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\Mission\Mission;
use App\Models\Game\StarmapLocation;
use App\Models\Game\Vehicle;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\StarCitizen\Galactapedia\Article;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate {--only= : Comma-separated list of segments to generate}';

    protected $description = 'Generate sitemap files for search engines';

    private const array SITEMAP_FILES = [
        'static' => 'sitemap-static.xml',
        'comm-links' => 'sitemap-comm-links.xml',
        'galactapedia' => 'sitemap-galactapedia.xml',
        'vehicles' => 'sitemap-vehicles.xml',
        'items' => 'sitemap-items.xml',
        'blueprints' => 'sitemap-blueprints.xml',
        'commodities' => 'sitemap-commodities.xml',
        'missions' => 'sitemap-missions.xml',
        'locations' => 'sitemap-locations.xml',
    ];

    private const int CHUNK_SIZE = 1000;

    private const array GAME_SEGMENTS = ['vehicles', 'items', 'blueprints', 'missions', 'locations'];

    public function handle(): int
    {
        $available = array_keys(self::SITEMAP_FILES);
        $only = (string) $this->option('only');
        $segments = $only !== '' ? explode(',', $only) : $available;
        $segments = array_values(array_intersect($segments, $available));

        if ($segments === []) {
            $this->error('No valid segments specified. Available: '.implode(', ', $available));

            return self::FAILURE;
        }

        $gameSegments = array_intersect($segments, self::GAME_SEGMENTS);
        if ($gameSegments !== [] && ! GameVersion::query()->where('is_default', true)->exists()) {
            $this->warn('No default game version found, skipping segments: '.implode(', ', $gameSegments).'.');
            $segments = array_values(array_diff($segments, self::GAME_SEGMENTS));

            // Stale segment files were built against the previous default version;
            // delete them so generateIndex() cannot keep referencing dead URLs.
            foreach ($gameSegments as $segment) {
                $path = storage_path('app/sitemaps/'.self::SITEMAP_FILES[$segment]);

                if (File::exists($path)) {
                    File::delete($path);
                }
            }
        }

        File::ensureDirectoryExists(storage_path('app/sitemaps'));

        foreach ($segments as $segment) {
            $this->info("Generating sitemap segment: {$segment}");
            $this->generateSegment($segment);
        }

        $this->pruneRetiredSegmentFiles();

        $this->info('Generating sitemap index...');
        $this->generateIndex();

        $this->info('Sitemap generated successfully.');

        return self::SUCCESS;
    }

    private function generateSegment(string $segment): void
    {
        $sitemap = Sitemap::create();

        match ($segment) {
            'static' => $this->addStaticUrls($sitemap),
            'comm-links' => $this->addCommLinks($sitemap),
            'galactapedia' => $this->addGalactapedia($sitemap),
            'vehicles' => $this->addCurrentVersionModelUrls($sitemap, Vehicle::class, 'web.vehicles.show'),
            'items' => $this->addCurrentVersionModelUrls($sitemap, Item::class, 'web.items.show', excludeVehicleItems: true),
            'blueprints' => $this->addCurrentVersionModelUrls($sitemap, Blueprint::class, 'web.blueprints.show'),
            'commodities' => $this->addModelUrls($sitemap, Commodity::class, 'web.commodities.show'),
            'missions' => $this->addCurrentVersionModelUrls($sitemap, Mission::class, 'web.missions.show'),
            'locations' => $this->addCurrentVersionModelUrls($sitemap, StarmapLocation::class, 'web.locations.show', requireSystem: true),
        };

        $sitemap->writeToFile(storage_path('app/sitemaps/'.self::SITEMAP_FILES[$segment]));
    }

    private function generateIndex(): void
    {
        $index = SitemapIndex::create();

        foreach (self::SITEMAP_FILES as $file) {
            if (file_exists(storage_path('app/sitemaps/'.$file))) {
                $index->add(url('sitemaps/'.$file));
            }
        }

        $index->writeToFile(storage_path('app/sitemaps/sitemap.xml'));
    }

    private function addStaticUrls(Sitemap $sitemap): void
    {
        $routes = [
            ['home', 0.8, Url::CHANGE_FREQUENCY_MONTHLY],
            ['web.comm-links.index', 0.7, Url::CHANGE_FREQUENCY_DAILY],
            ['web.comm-links.search', 0.5, Url::CHANGE_FREQUENCY_WEEKLY],
            ['web.comm-links.images.index', 0.5, Url::CHANGE_FREQUENCY_DAILY],
            ['web.stats.index', 0.4, Url::CHANGE_FREQUENCY_DAILY],
            ['web.galactapedia.index', 0.7, Url::CHANGE_FREQUENCY_WEEKLY],
            ['web.vehicles.index', 0.7, Url::CHANGE_FREQUENCY_WEEKLY],
            ['web.blueprints.index', 0.7, Url::CHANGE_FREQUENCY_WEEKLY],
            ['web.blueprints.search', 0.5, Url::CHANGE_FREQUENCY_WEEKLY],
            ['web.items.index', 0.7, Url::CHANGE_FREQUENCY_WEEKLY],
            ['web.commodities.index', 0.7, Url::CHANGE_FREQUENCY_WEEKLY],
            ['web.missions.index', 0.7, Url::CHANGE_FREQUENCY_WEEKLY],
            ['web.locations.index', 0.7, Url::CHANGE_FREQUENCY_WEEKLY],
            ['web.ship-matrix.vehicles.index', 0.6, Url::CHANGE_FREQUENCY_MONTHLY],
            ['web.starmap.systems.index', 0.5, Url::CHANGE_FREQUENCY_MONTHLY],
            ['web.starmap.celestial-objects.index', 0.5, Url::CHANGE_FREQUENCY_MONTHLY],
        ];

        foreach ($routes as [$routeName, $priority, $changefreq]) {
            $sitemap->add(
                Url::create(route($routeName))
                    ->setPriority($priority)
                    ->setChangeFrequency($changefreq)
            );
        }
    }

    private function addCommLinks(Sitemap $sitemap): void
    {
        CommLink::query()
            ->select(['id', 'cig_id', 'created_at'])
            ->without(['channel', 'category', 'series'])
            ->chunkById(self::CHUNK_SIZE, function ($chunk) use ($sitemap): void {
                foreach ($chunk as $commLink) {
                    $sitemap->add(
                        Url::create(route('web.comm-links.show', $commLink->cig_id))
                            ->setLastModificationDate($commLink->created_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                            ->setPriority(0.8)
                    );
                }
            });
    }

    private function addGalactapedia(Sitemap $sitemap): void
    {
        Article::query()
            ->select(['id', 'cig_id', 'updated_at'])
            ->where('disabled', false)
            ->chunkById(self::CHUNK_SIZE, function ($chunk) use ($sitemap): void {
                foreach ($chunk as $article) {
                    $sitemap->add(
                        Url::create(route('web.galactapedia.show', $article->cig_id))
                            ->setLastModificationDate($article->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                }
            });
    }

    /**
     * Skip entities the web show routes would 404 or redirect.
     *
     * @param  class-string<Model>  $model
     */
    private function addCurrentVersionModelUrls(
        Sitemap $sitemap,
        string $model,
        string $routeName,
        bool $excludeVehicleItems = false,
        bool $requireSystem = false,
    ): void {
        $versionId = GameVersion::query()->where('is_default', true)->value('id');

        $defaultVersionData = function (Builder $data) use ($versionId, $requireSystem): void {
            $data->where('game_version_id', $versionId);

            if ($requireSystem) {
                $data->whereNotNull('system');
            }
        };

        $this->addModelUrls($sitemap, $model, $routeName,
            fn (Builder $query): Builder => $query
                ->whereHas('data', $defaultVersionData)
                ->when($excludeVehicleItems, fn (Builder $query): Builder => $query->whereDoesntHave('vehicle'))
        );
    }

    /**
     * Add detail URLs for a slugged model, keyed by slug with uuid fallback.
     *
     * @param  class-string<Model>  $model
     * @param  (callable(Builder $query): Builder)|null  $scope
     */
    private function addModelUrls(Sitemap $sitemap, string $model, string $routeName, ?callable $scope = null): void
    {
        $query = $model::query()
            ->select(['id', 'slug', 'uuid', 'updated_at']);

        if ($scope !== null) {
            $query = $scope($query);
        }

        $query->chunkById(self::CHUNK_SIZE, function ($chunk) use ($sitemap, $routeName): void {
            foreach ($chunk as $record) {
                $sitemap->add(
                    Url::create(route($routeName, $record->slug ?? $record->uuid))
                        ->setLastModificationDate($record->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setPriority(1.0)
                );
            }
        });
    }

    private function pruneRetiredSegmentFiles(): void
    {
        foreach (File::files(storage_path('app/sitemaps')) as $file) {
            $name = $file->getFilename();

            if (str_starts_with($name, 'sitemap-') && str_ends_with($name, '.xml') && ! in_array($name, self::SITEMAP_FILES, true)) {
                File::delete($file->getPathname());
            }
        }
    }
}
