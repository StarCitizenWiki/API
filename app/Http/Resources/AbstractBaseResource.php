<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class AbstractBaseResource extends JsonResource
{
    public const COMM_LINKS_SHOW = 'comm-links/';

    public const COMM_LINK_IMAGES_SIMILAR = 'comm-link-images/';

    public const VEHICLES_SHOW = 'vehicles/';

    public const SHIPMATRIX_VEHICLES_SHOW = 'shipmatrix/vehicles/';

    public const STARMAP_STARSYSTEM_SHOW = 'starsystems/';

    public const STARMAP_CELESTIAL_OBJECTS_SHOW = 'celestial-objects/';

    public const GALACTAPEDIA_ARTICLE_SHOW = 'galactapedia/';

    public const ITEMS_SHOW = 'items/';

    public const FOOD_SHOW = 'food/';

    public const PERSONAL_WEAPONS_SHOW = 'weapons/';

    public const ARMOR_SHOW = 'armor/';

    public const CLOTHES_SHOW = 'clothes/';

    public const SHOPS_SHOW = 'shops/';

    public const MANUFACTURERS_SHOW = 'manufacturers/';

    public const MISSIONS_SHOW = 'missions/';

    public const MISSION_GIVERS_SHOW = 'mission-givers/';

    public const FACTIONS_SHOW = 'factions/';

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
     * @param  mixed  ...$routeKey
     */
    protected function makeApiUrl(string $fragment, ...$routeKey): string
    {
        return sprintf('%s/api/%s%s', config('app.url'), $fragment, ...$routeKey);
    }

    public static function validIncludes(): array
    {
        return [
            'variants',
        ];
    }

    public function addMetadata(mixed $key, mixed $value = null): void
    {
        if (is_array($key)) {
            $this->additional['meta'] += $key;
        } else {
            $this->additional['meta'][$key] = $value;
        }
    }

    protected function cleanType(): string
    {
        return str_replace('NOITEM_', '', ($this->type ?? ''));
    }
}
