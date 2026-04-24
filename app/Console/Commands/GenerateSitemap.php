<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Game\Blueprint;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\Item;
use App\Models\Game\Mission\Mission;
use App\Models\Game\StarmapLocation;
use App\Models\Game\Vehicle;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Models\StarCitizen\Starmap\Starsystem;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate {--only= : Comma-separated list of segments to generate}';

    protected $description = 'Generate sitemap files for search engines';

    private const array SEGMENTS = [
        'static',
        'comm-links',
        'galactapedia',
        'vehicles',
        'items',
        'blueprints',
        'commodities',
        'missions',
        'locations',
        'starsystems',
        'celestial-objects',
    ];

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
        'starsystems' => 'sitemap-starsystems.xml',
        'celestial-objects' => 'sitemap-celestial-objects.xml',
    ];

    private const int CHUNK_SIZE = 1000;

    public function handle(): int
    {
        $only = (string) $this->option('only');
        $segments = $only !== '' ? explode(',', $only) : self::SEGMENTS;
        $segments = array_values(array_intersect($segments, self::SEGMENTS));

        if ($segments === []) {
            $this->error('No valid segments specified. Available: '.implode(', ', self::SEGMENTS));

            return self::FAILURE;
        }

        foreach ($segments as $segment) {
            $this->info("Generating sitemap segment: {$segment}");
            $this->generateSegment($segment);
        }

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
            'vehicles' => $this->addVehicles($sitemap),
            'items' => $this->addItems($sitemap),
            'blueprints' => $this->addBlueprints($sitemap),
            'commodities' => $this->addCommodities($sitemap),
            'missions' => $this->addMissions($sitemap),
            'locations' => $this->addLocations($sitemap),
            'starsystems' => $this->addStarsystems($sitemap),
            'celestial-objects' => $this->addCelestialObjects($sitemap),
        };

        $sitemap->writeToFile(public_path(self::SITEMAP_FILES[$segment]));
    }

    private function generateIndex(): void
    {
        $index = SitemapIndex::create();

        foreach (self::SITEMAP_FILES as $file) {
            if (file_exists(public_path($file))) {
                $index->add(url($file));
            }
        }

        $index->writeToFile(public_path('sitemap.xml'));
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
            ['web.ship-matrix.ground-vehicles.index', 0.6, Url::CHANGE_FREQUENCY_MONTHLY],
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

    private function addVehicles(Sitemap $sitemap): void
    {
        Vehicle::query()
            ->select(['id', 'slug', 'uuid', 'updated_at'])
            ->chunkById(self::CHUNK_SIZE, function ($chunk) use ($sitemap): void {
                foreach ($chunk as $vehicle) {
                    $sitemap->add(
                        Url::create(route('web.vehicles.show', $vehicle->slug ?? $vehicle->uuid))
                            ->setLastModificationDate($vehicle->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(1.0)
                    );
                }
            });
    }

    private function addItems(Sitemap $sitemap): void
    {
        Item::query()
            ->select(['id', 'slug', 'uuid', 'updated_at'])
            ->chunkById(self::CHUNK_SIZE, function ($chunk) use ($sitemap): void {
                foreach ($chunk as $item) {
                    $sitemap->add(
                        Url::create(route('web.items.show', $item->slug ?? $item->uuid))
                            ->setLastModificationDate($item->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(1.0)
                    );
                }
            });
    }

    private function addBlueprints(Sitemap $sitemap): void
    {
        Blueprint::query()
            ->select(['id', 'slug', 'uuid', 'updated_at'])
            ->chunkById(self::CHUNK_SIZE, function ($chunk) use ($sitemap): void {
                foreach ($chunk as $blueprint) {
                    $sitemap->add(
                        Url::create(route('web.blueprints.show', $blueprint->slug ?? $blueprint->uuid))
                            ->setLastModificationDate($blueprint->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(1.0)
                    );
                }
            });
    }

    private function addCommodities(Sitemap $sitemap): void
    {
        Commodity::query()
            ->select(['id', 'slug', 'uuid', 'updated_at'])
            ->chunkById(self::CHUNK_SIZE, function ($chunk) use ($sitemap): void {
                foreach ($chunk as $commodity) {
                    $sitemap->add(
                        Url::create(route('web.commodities.show', $commodity->slug ?? $commodity->uuid))
                            ->setLastModificationDate($commodity->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(1.0)
                    );
                }
            });
    }

    private function addMissions(Sitemap $sitemap): void
    {
        Mission::query()
            ->select(['id', 'slug', 'uuid', 'updated_at'])
            ->chunkById(self::CHUNK_SIZE, function ($chunk) use ($sitemap): void {
                foreach ($chunk as $mission) {
                    $sitemap->add(
                        Url::create(route('web.missions.show', $mission->slug ?? $mission->uuid))
                            ->setLastModificationDate($mission->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(1.0)
                    );
                }
            });
    }

    private function addLocations(Sitemap $sitemap): void
    {
        StarmapLocation::query()
            ->select(['id', 'slug', 'uuid', 'updated_at'])
            ->chunkById(self::CHUNK_SIZE, function ($chunk) use ($sitemap): void {
                foreach ($chunk as $location) {
                    $sitemap->add(
                        Url::create(route('web.locations.show', $location->slug ?? $location->uuid))
                            ->setLastModificationDate($location->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(1.0)
                    );
                }
            });
    }

    private function addStarsystems(Sitemap $sitemap): void
    {
        Starsystem::query()
            ->select(['id', 'code', 'time_modified'])
            ->without(['affiliation'])
            ->chunkById(self::CHUNK_SIZE, function ($chunk) use ($sitemap): void {
                foreach ($chunk as $system) {
                    $sitemap->add(
                        Url::create(route('web.starmap.systems.show', $system->code))
                            ->setLastModificationDate($system->time_modified)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(0.5)
                    );
                }
            });
    }

    private function addCelestialObjects(Sitemap $sitemap): void
    {
        CelestialObject::query()
            ->select(['id', 'code', 'time_modified'])
            ->without(['subtype', 'affiliation'])
            ->chunkById(self::CHUNK_SIZE, function ($chunk) use ($sitemap): void {
                foreach ($chunk as $object) {
                    $sitemap->add(
                        Url::create(route('web.starmap.celestial-objects.show', $object->code))
                            ->setLastModificationDate($object->time_modified)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(0.5)
                    );
                }
            });
    }
}
