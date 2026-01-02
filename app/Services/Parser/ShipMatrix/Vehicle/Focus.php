<?php

declare(strict_types=1);

namespace App\Services\Parser\ShipMatrix\Vehicle;

use App\Models\StarCitizen\Vehicle\Focus\Focus as VehicleFocus;
use App\Models\StarCitizen\Vehicle\Focus\FocusTranslation;
use App\Services\Parser\ShipMatrix\AbstractBaseElement as BaseElement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * Class Focus
 */
class Focus extends BaseElement
{
    private const VEHICLE_FOCUS = 'focus';

    private const FOCI = [
        'Gunship',
        'Gun Ship',
    ];

    private const FOCUS_NORMALIZED = 'Gunship';

    /**
     * Generates an array of given vehicle foci ids
     *
     * @return array all associated foci ids
     */
    public function getVehicleFociIDs(): array
    {
        $rawFocus = $this->rawData->get(self::VEHICLE_FOCUS);

        if ($rawFocus === null) {
            app('Log')::debug('Vehicle Focus not set in Matrix');

            return [];
        }

        $vehicleFoci = array_map('trim', preg_split('/(\/|\s-\s|,|\sand\s)/', $rawFocus));
        $vehicleFociIDs = [];

        app('Log')::debug('Vehicle Focus count: '.count($vehicleFoci));

        collect($vehicleFoci)->each(
            function ($vehicleFocus) use (&$vehicleFociIDs) {
                try {
                    $vehicleFocus = $this->getNormalizedFocus($vehicleFocus);

                    /** @var FocusTranslation $focus */
                    $focus = FocusTranslation::query()->where(
                        'translation',
                        $vehicleFocus
                    )->where(
                        'locale_code',
                        config('language.english')
                    )->firstOrFail();
                    $focus = $focus->focus;
                } catch (ModelNotFoundException $e) {
                    $focus = $this->createNewVehicleFocus($vehicleFocus);
                }

                $vehicleFociIDs[] = $focus->id;
            }
        );

        return $vehicleFociIDs;
    }

    /**
     * @return mixed|string
     */
    private function getNormalizedFocus(string $rawFocus)
    {
        if ($rawFocus !== null && is_string($rawFocus) && in_array($rawFocus, self::FOCI)) {
            $rawFocus = self::FOCUS_NORMALIZED;
        }

        return $rawFocus;
    }

    /**
     * Creates a new Vehicle Focus
     *
     * @param  string  $focus  English Focus Translation
     */
    private function createNewVehicleFocus(string $focus): VehicleFocus
    {
        /** @var VehicleFocus $vehicleFocus */
        $vehicleFocus = VehicleFocus::query()->updateOrCreate(
            [
                'slug' => Str::slug($focus),
            ],
            [
                'slug' => Str::slug($focus),
            ]
        );

        $vehicleFocus->translations()->updateOrCreate(
            [
                'locale_code' => config('language.english'),
            ], [
                'translation' => $focus,
            ]
        );

        return $vehicleFocus;
    }
}
