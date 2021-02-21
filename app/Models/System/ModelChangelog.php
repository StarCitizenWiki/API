<?php

declare(strict_types=1);

namespace App\Models\System;

use App\Models\Account\User\User;
use App\Models\Api\StarCitizen\Galactapedia\Article;
use App\Models\Api\StarCitizen\Galactapedia\ArticleTranslation;
use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Models\Api\StarCitizen\Manufacturer\ManufacturerTranslation;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNoteTranslation;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatusTranslation;
use App\Models\Api\StarCitizen\Vehicle\Focus\FocusTranslation;
use App\Models\Api\StarCitizen\Vehicle\Size\SizeTranslation;
use App\Models\Api\StarCitizen\Vehicle\Type\TypeTranslation;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinkTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Generic Model to hold all Changelogs as Json
 */
class ModelChangelog extends Model
{
    protected $fillable = [
        'type',
        'changelog',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'changelog' => 'collection',
    ];

    protected $with = [
        'user',
    ];

    /**
     * @return MorphTo
     */
    public function changelog(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Associated User
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns a link to the user who created the changelog
     *
     * @return string
     */
    public function getUserLinkAttribute(): string
    {
        if ($this->user === null) {
            return config('app.name');
        }

        return sprintf(
            '<a href="%s">%s</a>',
            route('web.user.users.edit', $this->user->getRouteKey()),
            $this->user->username
        );
    }

    /**
     * Returns a routable url to the detail page of the changed model
     *
     * @return string
     */
    public function getModelRouteAttribute(): string
    {
        $relation = $this->getRelation('changelog');

        switch ($this->changelog_type) {
            /** Set translation to vehicle */
            case VehicleTranslation::class:
                $relation = $relation->vehicle;
            case Vehicle::class:
                $route = 'ships';
                // Ugly
                if ($relation->size->english()->translation === 'vehicle') {
                    $route = 'ground-vehicles';
                }

                $route = route(
                    'web.user.starcitizen.vehicles.' . $route . '.edit',
                    $relation->getRouteKey(),
                );
                break;

            case CommLinkTranslation::class:
                /** Set translation to comm-link */
                $relation = $relation->commLink;
            case CommLink::class:
                $route = route(
                    'web.user.rsi.comm-links.show',
                    $relation->getRouteKey(),
                );
                break;

            case ManufacturerTranslation::class:
                $relation = $relation->manufacturer;
            case Manufacturer::class:
                $route = route(
                    'web.user.starcitizen.manufacturers.edit',
                    $relation->getRouteKey(),
                );
                break;

            case TypeTranslation::class:
                $route = route(
                    'web.user.starcitizen.vehicles.types.edit',
                    $relation->type->getRouteKey(),
                );
                break;

            case SizeTranslation::class:
                $route = route(
                    'web.user.starcitizen.vehicles.sizes.edit',
                    $relation->size->getRouteKey(),
                );
                break;

            case FocusTranslation::class:
                $route = route(
                    'web.user.starcitizen.vehicles.foci.edit',
                    $relation->focus->getRouteKey(),
                );
                break;

            case ProductionStatusTranslation::class:
                $route = route(
                    'web.user.starcitizen.production-statuses.edit',
                    $relation->productionStatus->getRouteKey(),
                );
                break;

            case ProductionNoteTranslation::class:
                $route = route(
                    'web.user.starcitizen.production-notes.edit',
                    $relation->productionNote->getRouteKey(),
                );
                break;

            case ArticleTranslation::class:
                $relation = $relation->article;
            case Article::class:
                $route = route(
                    'web.user.starcitizen.galactapedia.show',
                    $relation->getRouteKey(),
                );
                break;

            default:
                $route = '#';
        }

        return $route;
    }

    /**
     * Returns changelog data crudely formatted
     *
     * @return string
     *
     * @throws \JsonException
     */
    public function getFormattedChangelogAttribute(): string
    {
        $data = $this->attributesToArray()['changelog'];

        if ($data === null) {
            return __('Keine');
        }

        if ($this->type === 'creation') {
            return collect($data)->reduce(
                function ($carry, $data) {
                    $keys = array_keys($data)[0];

                    return $carry . sprintf('%s: %s<br>', $keys, $data[$keys]);
                },
                ''
            );
        }

        return json_encode($data['changes'], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }
}
