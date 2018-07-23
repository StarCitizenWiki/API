<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser;

use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNote;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNoteTranslation;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatusTranslation;
use App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus;
use App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocusTranslation;
use App\Models\Api\StarCitizen\Vehicle\Size\VehicleSize;
use App\Models\Api\StarCitizen\Vehicle\Size\VehicleSizeTranslation;
use App\Models\Api\StarCitizen\Vehicle\Type\VehicleType;
use App\Models\Api\StarCitizen\Vehicle\Type\VehicleTypeTranslation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Class AbstractParseVehicle
 */
abstract class AbstractParseVehicle implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected const LANGUAGE_EN = 'en_EN';
    protected const PRODUCTION_STATUS = 'production_status';
    protected const PRODUCTION_NOTE = 'production_note';

    protected const VEHICLE_NAME = 'name';
    protected const VEHICLE_TYPE = 'type';
    protected const VEHICLE_SIZE = 'size';
    protected const VEHICLE_ID = 'id';
    protected const VEHICLE_LENGTH = 'length';
    protected const VEHICLE_BEAM = 'beam';
    protected const VEHICLE_HEIGHT = 'height';
    protected const VEHICLE_MASS = 'mass';
    protected const VEHICLE_CARGO_CAPACITY = 'cargocapacity';
    protected const VEHICLE_MIN_CREW = 'min_crew';
    protected const VEHICLE_MAX_CREW = 'max_crew';
    protected const VEHICLE_SCM_SPEED = 'scm_speed';
    protected const VEHICLE_AFTERBURNER_SPEED = 'afterburner_speed';
    protected const VEHICLE_CHASSIS_ID = 'chassis_id';
    protected const VEHICLE_DESCRIPTION = 'description';

    protected const MANUFACTURER = 'manufacturer';
    protected const MANUFACTURER_ID = 'manufacturer_id';
    protected const MANUFACTURER_NAME = 'name';
    protected const MANUFACTURER_CODE = 'code';
    protected const MANUFACTURER_KNOWN_FOR = 'known_for';
    protected const MANUFACTURER_DESCRIPTION = 'description';

    protected const TIME_MODIFIED_UNFILTERED = 'time_modified.unfiltered';

    private const PRODUCTION_STATUSES = [
        'Update Pass Scheduled',
        'Update pass scheduled',
        'Update pass scheduled.',
    ];

    private const PRODUCTION_STATUS_NORMALIZED = 'Update Pass Scheduled';

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $rawData;

    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Support\Collection $rawData
     */
    public function __construct(Collection $rawData)
    {
        $this->rawData = $rawData;
    }

    /**
     * @return \App\Models\Api\StarCitizen\Manufacturer\Manufacturer
     */
    protected function getManufacturer(): Manufacturer
    {
        app('Log')::debug('Getting Manufacturer');

        try {
            /** @var \App\Models\Api\StarCitizen\Manufacturer\Manufacturer $manufacturer */
            $manufacturer = Manufacturer::where(
                'cig_id',
                $this->rawDataGet(self::MANUFACTURER_ID)
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Manufacturer not found in DB');

            return $this->createNewManufacturer();
        }

        app('Log')::debug('Manufacturer already in DB');

        return $manufacturer;
    }

    /**
     * @return \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus
     */
    protected function getProductionStatus(): ProductionStatus
    {
        app('Log')::debug('Getting Production Status');

        $status = $this->rawDataGet(self::PRODUCTION_STATUS);
        if (null === $status) {
            app('Log')::debug('Status not set in Matrix, returning default (undefined)');

            return ProductionStatus::find(1);
        }

        try {
            /** @var \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatusTranslation $productionStatusTranslation */
            $productionStatusTranslation = ProductionStatusTranslation::where(
                'translation',
                $status
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Production Status not found in DB');

            return $this->createNewProductionStatus();
        }

        return $productionStatusTranslation->productionStatus;
    }

    /**
     * @return \App\Models\Api\StarCitizen\ProductionNote\ProductionNote
     */
    protected function getProductionNote(): ProductionNote
    {
        app('Log')::debug('Getting Production Note');

        $note = $this->rawDataGet(self::PRODUCTION_NOTE);
        if (null === $note) {
            app('Log')::debug('Production Note not set in Matrix, returning default (None)');

            return ProductionNote::find(1);
        }

        try {
            /** @var \App\Models\Api\StarCitizen\ProductionNote\ProductionNoteTranslation $productionNoteTranslation */
            $productionNoteTranslation = ProductionNoteTranslation::where(
                'translation',
                $note
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Production Note not found in DB');

            return $this->createNewProductionNote();
        }

        return $productionNoteTranslation->productionNote;
    }

    /**
     * @return \App\Models\Api\StarCitizen\Vehicle\Size\VehicleSize
     */
    protected function getVehicleSize(): VehicleSize
    {
        app('Log')::debug('Getting Vehicle Size');

        $size = $this->rawDataGet(self::VEHICLE_SIZE);
        if (null === $size) {
            app('Log')::debug('Vehicle Size not set in Matrix, returning default (undefined)');

            return VehicleSize::find(1);
        }

        try {
            /** @var \App\Models\Api\StarCitizen\Vehicle\Size\VehicleSizeTranslation $sizeTranslation */
            $sizeTranslation = VehicleSizeTranslation::where(
                'translation',
                $size
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Vehicle Size not found in DB');

            return $this->createNewVehicleSize();
        }

        return $sizeTranslation->vehicleSize;
    }

    /**
     * @return \App\Models\Api\StarCitizen\Vehicle\Type\VehicleType
     */
    protected function getVehicleType(): VehicleType
    {
        app('Log')::debug('Getting Vehicle Type');

        try {
            /** @var \App\Models\Api\StarCitizen\Vehicle\Type\VehicleTypeTranslation $typeTranslation */
            $typeTranslation = VehicleTypeTranslation::where(
                'translation',
                $this->rawDataGet(self::VEHICLE_TYPE)
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Vehicle Type not found in DB');

            return $this->createNewVehicleType();
        }

        return $typeTranslation->vehicleType;
    }

    /**
     * Generates an array of given vehicle foci ids
     *
     * @return array all associated foci ids
     */
    protected function getVehicleFociIDs(): array
    {
        app('Log')::debug('Getting Vehicle Foci IDs');

        $rawFocus = $this->rawDataGet('focus');

        if (null === $rawFocus) {
            app('Log')::debug('Vehicle Focus not set in Matrix');

            return [];
        }

        $vehicleFoci = array_map('trim', preg_split('/(\/|-)/', $rawFocus));
        $vehicleFociIDs = [];

        app('Log')::debug('Vehicle Focus count: '.count($vehicleFoci));

        foreach ($vehicleFoci as $vehicleFocus) {
            try {
                /** @var \App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocusTranslation $focus */
                $focus = VehicleFocusTranslation::where('translation', $vehicleFocus)->firstOrFail();
                $focus = $focus->vehicleFocus;
            } catch (ModelNotFoundException $e) {
                $focus = $this->createNewVehicleFocus($vehicleFocus);
            }

            $vehicleFociIDs[] = $focus->id;
        }

        return $vehicleFociIDs;
    }

    /**
     * @return \App\Models\Api\StarCitizen\Manufacturer\Manufacturer
     */
    private function createNewManufacturer(): Manufacturer
    {
        app('Log')::debug('Creating new Manufacturer');

        $manufacturerData = $this->rawDataGet(self::MANUFACTURER);
        /** @var \App\Models\Api\StarCitizen\Manufacturer\Manufacturer $manufacturer */
        $manufacturer = Manufacturer::create(
            [
                'cig_id' => $this->rawDataGet(self::MANUFACTURER_ID),
                'name' => $manufacturerData->get(self::MANUFACTURER_NAME),
                'name_short' => $manufacturerData->get(self::MANUFACTURER_CODE),
            ]
        );

        $manufacturer->translations()->create(
            [
                'locale_code' => self::LANGUAGE_EN,
                'known_for' => $manufacturerData->get(self::MANUFACTURER_KNOWN_FOR),
                'description' => $manufacturerData->get(self::MANUFACTURER_DESCRIPTION),
            ]
        );

        app('Log')::debug('Manufacturer created');

        return $manufacturer;
    }

    /**
     * @return \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus
     */
    private function createNewProductionStatus(): ProductionStatus
    {
        app('Log')::debug('Creating new Production Status');

        /** @var \App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus $productionStatus */
        $productionStatus = ProductionStatus::create();
        $productionStatus->translations()->create(
            [
                'locale_code' => self::LANGUAGE_EN,
                'translation' => $this->rawDataGet(self::PRODUCTION_STATUS),
            ]
        );

        app('Log')::debug('Production Status created');

        return $productionStatus;
    }

    /**
     * @return \App\Models\Api\StarCitizen\ProductionNote\ProductionNote
     */
    private function createNewProductionNote(): ProductionNote
    {
        app('Log')::debug('Creating new Production Note');

        /** @var \App\Models\Api\StarCitizen\ProductionNote\ProductionNote $productionNote */
        $productionNote = ProductionNote::create();
        $productionNote->translations()->create(
            [
                'locale_code' => self::LANGUAGE_EN,
                'translation' => $this->rawDataGet(self::PRODUCTION_NOTE),
            ]
        );

        app('Log')::debug('Production Note created');

        return $productionNote;
    }

    /**
     * @return \App\Models\Api\StarCitizen\Vehicle\Size\VehicleSize
     */
    private function createNewVehicleSize(): VehicleSize
    {
        app('Log')::debug('Creating new Vehicle Size');

        $vehicleSize = VehicleSize::create();
        $vehicleSize->translations()->create(
            [
                'locale_code' => self::LANGUAGE_EN,
                'translation' => $this->rawDataGet(self::VEHICLE_SIZE),
            ]
        );

        app('Log')::debug('Vehicle Size created');

        return $vehicleSize;
    }

    /**
     * @return \App\Models\Api\StarCitizen\Vehicle\Type\VehicleType
     */
    private function createNewVehicleType(): VehicleType
    {
        app('Log')::debug('Creating new Vehicle Type');

        $vehicleType = VehicleType::create();
        $vehicleType->translations()->create(
            [
                'locale_code' => self::LANGUAGE_EN,
                'translation' => $this->rawDataGet(self::VEHICLE_TYPE),
            ]
        );

        app('Log')::debug('Vehicle Type created');

        return $vehicleType;
    }

    /**
     * Creates a new Vehicle Focus
     *
     * @param string $focus English Focus Translation
     *
     * @return \App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus
     */
    private function createNewVehicleFocus(string $focus): VehicleFocus
    {
        app('Log')::debug('Creating new Vehicle Focus');

        /** @var \App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus $vehicleFocus */
        $vehicleFocus = VehicleFocus::create();
        $vehicleFocus->translations()->create(
            [
                'locale_code' => self::LANGUAGE_EN,
                'translation' => $focus,
            ]
        );

        return $vehicleFocus;
    }

    /**
     * Simple Collection get Wrapper that normalizes the raw data
     *
     * @param mixed $key Key to Search for
     *
     * @return string|mixed
     */
    private function rawDataGet($key)
    {
        $data = $this->rawData->get($key);

        if (null !== $data && is_string($data)) {
            $data = rtrim($data, '.');

            if (in_array($data, self::PRODUCTION_STATUSES)) {
                $data = self::PRODUCTION_STATUS_NORMALIZED;
            }
        }


        return $data;
    }
}
