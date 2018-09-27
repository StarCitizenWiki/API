<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 25.09.2018
 * Time: 12:55
 */

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\Vehicle;

use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\AbstractBaseElement as BaseElement;
use App\Models\Api\StarCitizen\Vehicle\Focus\Focus as VehicleFocus;
use App\Models\Api\StarCitizen\Vehicle\Focus\FocusTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        app('Log')::debug('Getting Vehicle Foci IDs');

        $rawFocus = $this->rawData->get(self::VEHICLE_FOCUS);

        if (null === $rawFocus) {
            app('Log')::debug('Vehicle Focus not set in Matrix');

            return [];
        }

        $vehicleFoci = array_map('trim', preg_split('/(\/|-)/', $rawFocus));
        $vehicleFociIDs = [];

        app('Log')::debug('Vehicle Focus count: '.count($vehicleFoci));

        collect($vehicleFoci)->each(
            function ($vehicleFocus) use (&$vehicleFociIDs) {
                try {
                    $vehicleFocus = $this->getNormalizedFocus($vehicleFocus);

                    /** @var \App\Models\Api\StarCitizen\Vehicle\Focus\FocusTranslation $focus */
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
     * Creates a new Vehicle Focus
     *
     * @param string $focus English Focus Translation
     *
     * @return \App\Models\Api\StarCitizen\Vehicle\Focus\Focus
     */
    private function createNewVehicleFocus(string $focus): VehicleFocus
    {
        app('Log')::debug('Creating new Vehicle Focus');

        /** @var \App\Models\Api\StarCitizen\Vehicle\Focus\Focus $vehicleFocus */
        $vehicleFocus = VehicleFocus::create(
            [
                'slug' => str_slug($focus),
            ]
        );

        $vehicleFocus->translations()->create(
            [
                'locale_code' => config('language.english'),
                'translation' => $focus,
            ]
        );

        return $vehicleFocus;
    }

    /**
     * @param string $rawFocus
     *
     * @return mixed|string
     */
    private function getNormalizedFocus(string $rawFocus)
    {
        if (null !== $rawFocus && is_string($rawFocus)) {
            if (in_array($rawFocus, self::FOCI)) {
                $rawFocus = self::FOCUS_NORMALIZED;
            }
        }

        return $rawFocus;
    }
}
