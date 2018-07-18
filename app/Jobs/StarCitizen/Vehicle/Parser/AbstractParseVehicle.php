<?php declare(strict_types = 1);

namespace App\Jobs\StarCitizen\Vehicle\Parser;

use App\Models\StarCitizen\Manufacturer\Manufacturer;
use App\Models\StarCitizen\ProductionNote\ProductionNote;
use App\Models\StarCitizen\ProductionNote\ProductionNoteTranslation;
use App\Models\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\StarCitizen\ProductionStatus\ProductionStatusTranslation;
use App\Models\StarCitizen\Vehicle\Focus\VehicleFocus;
use App\Models\StarCitizen\Vehicle\Focus\VehicleFocusTranslation;
use App\Models\StarCitizen\Vehicle\Size\VehicleSize;
use App\Models\StarCitizen\Vehicle\Size\VehicleSizeTranslation;
use App\Models\StarCitizen\Vehicle\Type\VehicleType;
use App\Models\StarCitizen\Vehicle\Type\VehicleTypeTranslation;
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

    protected const LANGUAGE_EN = 1;
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
     * @return \App\Models\StarCitizen\Manufacturer\Manufacturer
     */
    protected function getManufacturer(): Manufacturer
    {
        app('Log')::debug('Getting Manufacturer');

        try {
            /** @var \App\Models\StarCitizen\Manufacturer\Manufacturer $manufacturer */
            $manufacturer = Manufacturer::where(
                'cig_id',
                $this->rawData->get(self::MANUFACTURER_ID)
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Manufacturer not found in DB');

            return $this->createNewManufacturer();
        }

        app('Log')::debug('Manufacturer already in DB');

        return $manufacturer;
    }

    /**
     * @return \App\Models\StarCitizen\ProductionStatus\ProductionStatus
     */
    protected function getProductionStatus(): ProductionStatus
    {
        app('Log')::debug('Getting Production Status');

        $status = $this->rawData->get(self::PRODUCTION_STATUS);
        if (null === $status) {
            app('Log')::debug('Status not set in Matrix, returning default (undefined)');

            return ProductionStatus::find(1);
        }

        try {
            /** @var \App\Models\StarCitizen\ProductionStatus\ProductionStatusTranslation $productionStatusTranslation */
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
     * @return \App\Models\StarCitizen\ProductionNote\ProductionNote
     */
    protected function getProductionNote(): ProductionNote
    {
        app('Log')::debug('Getting Production Note');

        $note = $this->rawData->get(self::PRODUCTION_NOTE);
        if (null === $note) {
            app('Log')::debug('Production Note not set in Matrix, returning default (None)');

            return ProductionNote::find(1);
        }

        try {
            /** @var \App\Models\StarCitizen\ProductionNote\ProductionNoteTranslation $productionNoteTranslation */
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
     * @return \App\Models\StarCitizen\Vehicle\Size\VehicleSize
     */
    protected function getVehicleSize(): VehicleSize
    {
        app('Log')::debug('Getting Vehicle Size');

        $size = $this->rawData->get(self::VEHICLE_SIZE);
        if (null === $size) {
            app('Log')::debug('Vehicle Size not set in Matrix, returning default (undefined)');

            return VehicleSize::find(1);
        }

        try {
            /** @var \App\Models\StarCitizen\Vehicle\Size\VehicleSizeTranslation $sizeTranslation */
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
     * @return \App\Models\StarCitizen\Vehicle\Type\VehicleType
     */
    protected function getVehicleType(): VehicleType
    {
        app('Log')::debug('Getting Vehicle Type');

        try {
            /** @var \App\Models\StarCitizen\Vehicle\Type\VehicleTypeTranslation $typeTranslation */
            $typeTranslation = VehicleTypeTranslation::where(
                'translation',
                $this->rawData->get(self::VEHICLE_TYPE)
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

        $rawFocus = $this->rawData->get('focus');

        if (null === $rawFocus) {
            app('Log')::debug('Vehicle Focus not set in Matrix');

            return [];
        }

        $vehicleFoci = array_map('trim', explode('/', $rawFocus));
        $vehicleFociIDs = [];

        app('Log')::debug('Vehicle Focus count: '.count($vehicleFoci));

        foreach ($vehicleFoci as $vehicleFocus) {
            try {
                /** @var \App\Models\StarCitizen\Vehicle\Focus\VehicleFocusTranslation $focus */
                $focus = VehicleFocusTranslation::where('focus', $vehicleFocus)->firstOrFail();
                $focus = $focus->vehicleFocus;
            } catch (ModelNotFoundException $e) {
                $focus = $this->createNewVehicleFocus($vehicleFocus);
            }

            $vehicleFociIDs[] = $focus->id;
        }

        return $vehicleFociIDs;
    }

    /**
     * @return \App\Models\StarCitizen\ProductionStatus\ProductionStatus
     */
    private function createNewProductionStatus(): ProductionStatus
    {
        app('Log')::debug('Creating new Production Status');

        /** @var \App\Models\StarCitizen\ProductionStatus\ProductionStatus $productionStatus */
        $productionStatus = ProductionStatus::create();
        $productionStatus->translations()->create(
            [
                'language_id' => self::LANGUAGE_EN,
                'translation' => $this->rawData->get(self::PRODUCTION_STATUS),
            ]
        );

        app('Log')::debug('Production Status created');

        return $productionStatus;
    }

    /**
     * @return \App\Models\StarCitizen\Manufacturer\Manufacturer
     */
    private function createNewManufacturer(): Manufacturer
    {
        app('Log')::debug('Creating new Manufacturer');

        $manufacturerData = $this->rawData->get(self::MANUFACTURER);
        /** @var \App\Models\StarCitizen\Manufacturer\Manufacturer $manufacturer */
        $manufacturer = Manufacturer::create(
            [
                'cig_id' => $this->rawData->get(self::MANUFACTURER_ID),
                'name' => $manufacturerData->get(self::MANUFACTURER_NAME),
                'name_short' => $manufacturerData->get(self::MANUFACTURER_CODE),
            ]
        );

        $manufacturer->translations()->create(
            [
                'language_id' => self::LANGUAGE_EN,
                'known_for' => $manufacturerData->get(self::MANUFACTURER_KNOWN_FOR),
                'description' => $manufacturerData->get(self::MANUFACTURER_DESCRIPTION),
            ]
        );

        app('Log')::debug('Manufacturer created');

        return $manufacturer;
    }

    /**
     * @return \App\Models\StarCitizen\ProductionNote\ProductionNote
     */
    private function createNewProductionNote(): ProductionNote
    {
        app('Log')::debug('Creating new Production Note');

        /** @var \App\Models\StarCitizen\ProductionNote\ProductionNote $productionNote */
        $productionNote = ProductionNote::create();
        $productionNote->translations()->create(
            [
                'language_id' => self::LANGUAGE_EN,
                'translation' => $this->rawData->get(self::PRODUCTION_NOTE),
            ]
        );

        app('Log')::debug('Production Note created');

        return $productionNote;
    }

    /**
     * @return \App\Models\StarCitizen\Vehicle\Size\VehicleSize
     */
    private function createNewVehicleSize(): VehicleSize
    {
        app('Log')::debug('Creating new Vehicle Size');

        $vehicleSize = VehicleSize::create();
        $vehicleSize->translations()->create(
            [
                'language_id' => self::LANGUAGE_EN,
                'translation' => $this->rawData->get(self::VEHICLE_SIZE),
            ]
        );

        app('Log')::debug('Vehicle Size created');

        return $vehicleSize;
    }

    /**
     * @return \App\Models\StarCitizen\Vehicle\Type\VehicleType
     */
    private function createNewVehicleType(): VehicleType
    {
        app('Log')::debug('Creating new Vehicle Type');

        $vehicleType = VehicleType::create();
        $vehicleType->translations()->create(
            [
                'language_id' => self::LANGUAGE_EN,
                'translation' => $this->rawData->get(self::VEHICLE_TYPE),
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
     * @return \App\Models\StarCitizen\Vehicle\Focus\VehicleFocus
     */
    private function createNewVehicleFocus(string $focus): VehicleFocus
    {
        app('Log')::debug('Creating new Vehicle Focus');

        /** @var \App\Models\StarCitizen\Vehicle\Focus\VehicleFocus $vehicleFocus */
        $vehicleFocus = VehicleFocus::create();
        $vehicleFocus->translations()->create(
            [
                'language_id' => self::LANGUAGE_EN,
                'translation' => $focus,
            ]
        );

        return $vehicleFocus;
    }
}
