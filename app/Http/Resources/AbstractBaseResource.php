<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class AbstractBaseResource extends JsonResource
{
    public const COMM_LINKS_SHOW = 'comm-links/%d';
    public const COMM_LINKS_SERIES_SHOW = 'comm-links/series';
    public const COMM_LINKS_CHANNELS_SHOW = 'comm-links/channels';
    public const COMM_LINKS_CATEGORIES_SHOW = 'comm-links/categories';

    public const VEHICLES_SHOW = 'vehicles';

    public const STARMAP_STARSYSTEM_SHOW = 'starmap/starsystems';
    public const STARMAP_CELESTIAL_OBJECTS_SHOW = 'starmap/celestial-objects';

    public const GALACTAPEDIA_ARTICLE_SHOW = 'galactapedia';



    public const UNPACKED_FOOD_SHOW = 'food/';

    public const UNPACKED_PERSONAL_WEAPONS_SHOW = 'personal-weapons/';
    public const UNPACKED_ARMOR_SHOW = 'armor/';
    public const UNPACKED_CLOTHES_SHOW = 'clothes/';
    public const UNPACKED_SHOPS_SHOW = 'shops/';

    public function __construct($resource)
    {
        parent::__construct($resource);

        $this->additional['meta'] = [
            'processed_at' => Carbon::now()->toDateTimeString(),
            'valid_relations' => static::validIncludes(),
        ];
    }

    /**
     * Formats the fragment and returns an absolute api url
     *
     * @param string $fragment
     * @param mixed ...$routeKey
     *
     * @return string
     */
    protected function makeApiUrl(string $fragment, ...$routeKey): string
    {
        return sprintf('%s/api/v2/%s%s', config('app.url'), $fragment, ...$routeKey);
    }

    public static function validIncludes(): array
    {
        return [];
    }

    public function addMetadata(mixed $key, mixed $value = null): void
    {
        if (is_array($key)) {
            $this->additional['meta'] += $key;
        } else {
            $this->additional['meta'][$key] = $value;
        }
    }
}
