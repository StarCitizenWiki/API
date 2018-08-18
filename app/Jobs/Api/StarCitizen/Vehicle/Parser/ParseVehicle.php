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
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
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
class ParseVehicle implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected const LANGUAGE_EN = 'en_EN';
    protected const PRODUCTION_STATUS = 'production_status';
    protected const PRODUCTION_NOTE = 'production_note';

    protected const VEHICLE_ID = 'id';
    protected const VEHICLE_CHASSIS_ID = 'chassis_id';
    protected const VEHICLE_NAME = 'name';
    protected const VEHICLE_LENGTH = 'length';
    protected const VEHICLE_BEAM = 'beam';
    protected const VEHICLE_HEIGHT = 'height';
    protected const VEHICLE_MASS = 'mass';
    protected const VEHICLE_CARGO_CAPACITY = 'cargocapacity';
    protected const VEHICLE_MIN_CREW = 'min_crew';
    protected const VEHICLE_MAX_CREW = 'max_crew';
    protected const VEHICLE_SCM_SPEED = 'scm_speed';
    protected const VEHICLE_AFTERBURNER_SPEED = 'afterburner_speed';
    protected const VEHICLE_PITCH_MAX = 'pitch_max';
    protected const VEHICLE_YAW_MAX = 'yaw_max';
    protected const VEHICLE_ROLL_MAX = 'roll_max';
    protected const VEHICLE_X_AXIS_ACCELERATION = 'xaxis_acceleration';
    protected const VEHICLE_Y_AXIS_ACCELERATION = 'yaxis_acceleration';
    protected const VEHICLE_Z_AXIS_ACCELERATION = 'zaxis_acceleration';
    protected const VEHICLE_TYPE = 'type';
    protected const VEHICLE_SIZE = 'size';
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info("Parsing Vehicle {$this->rawData->get(self::VEHICLE_NAME)}");

        /** @var \App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle $vehicle */
        $vehicle = Vehicle::updateOrCreate(
            [
                'cig_id' => (int) $this->rawData->get(self::VEHICLE_ID),
            ],
            [
                'name' => (string) $this->rawData->get(self::VEHICLE_NAME),
                'manufacturer_id' => (int) $this->getManufacturer()->id,
                'production_status_id' => (int) $this->getProductionStatus()->id,
                'production_note_id' => (int) $this->getProductionNote()->id,
                'vehicle_size_id' => (int) $this->getVehicleSize()->id,
                'vehicle_type_id' => (int) $this->getVehicleType()->id,
                'length' => number_format((float) $this->rawData->get(self::VEHICLE_LENGTH), 2, '.', ''),
                'beam' => number_format((float) $this->rawData->get(self::VEHICLE_BEAM), 2, '.', ''),
                'height' => number_format((float) $this->rawData->get(self::VEHICLE_HEIGHT), 2, '.', ''),
                'mass' => (int) $this->rawData->get(self::VEHICLE_MASS),
                'cargo_capacity' => (int) $this->rawData->get(self::VEHICLE_CARGO_CAPACITY),
                'min_crew' => (int) $this->rawData->get(self::VEHICLE_MIN_CREW),
                'max_crew' => (int) $this->rawData->get(self::VEHICLE_MAX_CREW),
                'scm_speed' => (int) $this->rawData->get(self::VEHICLE_SCM_SPEED),
                'afterburner_speed' => (int) $this->rawData->get(self::VEHICLE_AFTERBURNER_SPEED),
                'pitch_max' => number_format((float) $this->rawData->get(self::VEHICLE_PITCH_MAX), 2, '.', ''),
                'yaw_max' => number_format((float) $this->rawData->get(self::VEHICLE_YAW_MAX), 2, '.', ''),
                'roll_max' => number_format((float) $this->rawData->get(self::VEHICLE_ROLL_MAX), 2, '.', ''),
                'x_axis_acceleration' => number_format((float) $this->rawData->get(self::VEHICLE_X_AXIS_ACCELERATION), 2, '.', ''),
                'y_axis_acceleration' => number_format((float) $this->rawData->get(self::VEHICLE_Y_AXIS_ACCELERATION), 2, '.', ''),
                'z_axis_acceleration' => number_format((float) $this->rawData->get(self::VEHICLE_Z_AXIS_ACCELERATION), 2, '.', ''),
                'chassis_id' => (int) $this->rawData->get(self::VEHICLE_CHASSIS_ID),
                'updated_at' => $this->rawData->get(self::TIME_MODIFIED_UNFILTERED),
            ]
        );

        $vehicle->translations()->updateOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'locale_code' => self::LANGUAGE_EN,
            ],
            [
                'translation' => $this->rawData->get(self::VEHICLE_DESCRIPTION),
            ]
        );

        $fociIDsOld = [];
        $fociIDs = $this->getVehicleFociIDs();

        foreach ($vehicle->foci as $focus) {
            $fociIDsOld[] = $focus->id;
        }

        $fociIDsOld = array_values(array_sort($fociIDsOld));
        $fociIDs = array_values(array_sort($fociIDs));


        if ($fociIDsOld !== $fociIDs) {
            $changes = [
                'foci' => [
                    'old' => $fociIDsOld,
                    'new' => $fociIDs,
                ],
            ];

            if (!$vehicle->wasRecentlyCreated) {
                $vehicle->changelogs()->create(
                    [
                        'changelog' => json_encode($changes),
                    ]
                );

                app('Log')::debug('Updated ship_vehicle_focus', $changes);
            }

            $vehicle->foci()->sync($this->getVehicleFociIDs());
        }
    }

    /**
     * @return \App\Models\Api\StarCitizen\Manufacturer\Manufacturer
     */
    protected function getManufacturer(): Manufacturer
    {
        app('Log')::debug('Getting Manufacturer');

        $manufacturerData = collect($this->rawDataGet(self::MANUFACTURER));
        /** @var \App\Models\Api\StarCitizen\Manufacturer\Manufacturer $manufacturer */
        $manufacturer = Manufacturer::updateOrCreate(
            [
                'cig_id' => $this->rawDataGet(self::MANUFACTURER_ID),
            ],
            [
                'name' => htmlspecialchars_decode($manufacturerData->get(self::MANUFACTURER_NAME)),
                'name_short' => $manufacturerData->get(self::MANUFACTURER_CODE),
            ]
        );

        $manufacturer->translations()->updateOrCreate(
            [
                'manufacturer_id' => $manufacturer->id,
                'locale_code' => self::LANGUAGE_EN,
            ],
            [
                'known_for' => $manufacturerData->get(self::MANUFACTURER_KNOWN_FOR),
                'description' => $manufacturerData->get(self::MANUFACTURER_DESCRIPTION),
            ]
        );

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
