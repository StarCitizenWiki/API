<?php

namespace App\Http\Resources;

use App\Http\Resources\Rsi\CommLink\CommLinkResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class AbstractBaseResource extends JsonResource
{
    public const COMM_LINKS_SHOW = '/api/comm-links/%d';
    public const COMM_LINKS_SERIES_SHOW = '/api/comm-links/series/%s';
    public const COMM_LINKS_CHANNELS_SHOW = '/api/comm-links/channels/%s';
    public const COMM_LINKS_CATEGORIES_SHOW = '/api/comm-links/categories/%s';

    public const VEHICLES_SHOW = '/api/vehicles/%s';

    public const STARMAP_STARSYSTEM_SHOW = '/api/starmap/starsystems/%s';
    public const STARMAP_CELESTIAL_OBJECTS_SHOW = '/api/starmap/celestial-objects/%s';

    public const GALACTAPEDIA_ARTICLE_SHOW = '/api/galactapedia/%s';

    public const UNPACKED_CHAR_ARMOR_SHOW = '/api/char/armor/%s';
    public const UNPACKED_WEAPON_PERSONAL_SHOW = '/api/weapons/personal/%s';
    public const UNPACKED_CLOTHING_SHOW = '/api/char/clothing/%s';

    public const UNPACKED_FOOD_SHOW = '/api/food/%s';

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
     * @param mixed  ...$routeKey
     *
     * @return string
     */
    protected function makeApiUrl(string $fragment, ...$routeKey): string
    {
        return sprintf('%s' . $fragment, config('app.url'), ...$routeKey);
    }

    abstract public static function validIncludes(): array;

    public function addMetadata(mixed $key, mixed $value = null): void
    {
        if (is_array($key)) {
            $this->additional['meta'] += $key;
        } else {
            $this->additional['meta'][$key] = $value;
        }
    }
}
