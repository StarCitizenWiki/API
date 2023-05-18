<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Vehicle\Import;

use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Services\Parser\ShipMatrix\Component;
use App\Services\Parser\ShipMatrix\Manufacturer;
use App\Services\Parser\ShipMatrix\ProductionNote;
use App\Services\Parser\ShipMatrix\ProductionStatus;
use App\Services\Parser\ShipMatrix\Vehicle\Focus;
use App\Services\Parser\ShipMatrix\Vehicle\Size;
use App\Services\Parser\ShipMatrix\Vehicle\Type;
use App\Traits\CreateRelationChangelogTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class AbstractParseVehicle
 */
class ImportVehicle implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use CreateRelationChangelogTrait;

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

    protected const VEHICLE_DESCRIPTION = 'description';

    protected const TIME_MODIFIED_UNFILTERED = 'time_modified.unfiltered';

    /**
     * @var Collection
     */
    protected Collection $rawData;

    /**
     * Create a new job instance.
     *
     * @param Collection $rawData
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
    public function handle(): void
    {
        app('Log')::info(
            "Parsing Vehicle {$this->rawData->get(self::VEHICLE_NAME)}",
            [
                'vehicle' => $this->rawData->get(self::VEHICLE_NAME),
            ]
        );

        $data = $this->getData();
        $where = [
            'cig_id' => $data['id'],
        ];

        unset($data['id']);

        /** @var Vehicle $vehicle */
        $vehicle = Vehicle::query()->updateOrCreate($where, $data);

        $vehicle->translations()->updateOrCreate(
            [
                'locale_code' => config('language.english'),
            ],
            [
                'translation' => strip_tags($this->rawData->get(self::VEHICLE_DESCRIPTION, '') ?? ''),
            ]
        );

        $changes = [];
        $changes['foci'] = $this->syncFociIds($vehicle);
        $changes['components'] = $this->syncComponents($vehicle);

        $this->createRelationChangelog($changes, $vehicle);
    }

    /**
     * @return array Parsed Vehicle Data
     */
    public function getData(): array
    {
        return [
            'id' => (int)$this->rawData->get(self::VEHICLE_ID),
            'name' => trim($this->rawData->get(self::VEHICLE_NAME)),
            'slug' => Str::slug($this->rawData->get(self::VEHICLE_NAME)),
            'manufacturer_id' => $this->getManufacturerId(),
            'production_status_id' => $this->getProductionStatusId(),
            'production_note_id' => $this->getProductionNoteId(),
            'size_id' => $this->getSizeId(),
            'type_id' => $this->getTypeId(),
            'length' => $this->formatNum($this->rawData->get(self::VEHICLE_LENGTH)),
            'beam' => $this->formatNum($this->rawData->get(self::VEHICLE_BEAM)),
            'height' => $this->formatNum($this->rawData->get(self::VEHICLE_HEIGHT)),
            'mass' => (int)$this->rawData->get(self::VEHICLE_MASS),
            'cargo_capacity' => (int)$this->rawData->get(self::VEHICLE_CARGO_CAPACITY),
            'min_crew' => (int)$this->rawData->get(self::VEHICLE_MIN_CREW),
            'max_crew' => (int)$this->rawData->get(self::VEHICLE_MAX_CREW),
            'scm_speed' => (int)$this->rawData->get(self::VEHICLE_SCM_SPEED),
            'afterburner_speed' => (int)$this->rawData->get(self::VEHICLE_AFTERBURNER_SPEED),
            'pitch_max' => $this->formatNum($this->rawData->get(self::VEHICLE_PITCH_MAX)),
            'yaw_max' => $this->formatNum($this->rawData->get(self::VEHICLE_YAW_MAX)),
            'roll_max' => $this->formatNum($this->rawData->get(self::VEHICLE_ROLL_MAX)),
            'x_axis_acceleration' => $this->formatNum($this->rawData->get(self::VEHICLE_X_AXIS_ACCELERATION)),
            'y_axis_acceleration' => $this->formatNum($this->rawData->get(self::VEHICLE_Y_AXIS_ACCELERATION)),
            'z_axis_acceleration' => $this->formatNum($this->rawData->get(self::VEHICLE_Z_AXIS_ACCELERATION)),
            'chassis_id' => (int)$this->rawData->get(self::VEHICLE_CHASSIS_ID),
            'updated_at' => $this->rawData->get(self::TIME_MODIFIED_UNFILTERED),
        ];
    }

    /**
     * @return int
     */
    private function getManufacturerId(): int
    {
        $manufacturer = new Manufacturer($this->rawData);

        return $manufacturer->getManufacturer()->id;
    }

    /**
     * @return int
     */
    private function getProductionStatusId(): int
    {
        $productionStatus = new ProductionStatus($this->rawData);

        try {
            $id = $productionStatus->getProductionStatus()->id;
        } catch (ModelNotFoundException $e) {
            $this->fail('Could not get default Production Status. Was the database seeded?');

            return 0; // Does not happen
        }

        return $id;
    }

    /**
     * @return int
     */
    private function getProductionNoteId(): int
    {
        $productionNote = new ProductionNote($this->rawData);

        try {
            $id = $productionNote->getProductionNote()->id;
        } catch (ModelNotFoundException $e) {
            $this->fail('Could not get default Production Node. Was the database seeded?');

            return 0; // Does not happen
        }

        return $id;
    }

    /**
     * @return int
     */
    private function getSizeId(): int
    {
        $size = new Size($this->rawData);

        try {
            $id = $size->getVehicleSize()->id;
        } catch (ModelNotFoundException $e) {
            $this->fail('Could not get default Vehicle Size. Was the database seeded?');

            return 0; // Does not happen
        }

        return $id;
    }

    /**
     * @return int
     */
    private function getTypeId(): int
    {
        $type = new Type($this->rawData);

        try {
            $id = $type->getVehicleType()->id;
        } catch (ModelNotFoundException $e) {
            $this->fail('Could not get default Vehicle Type. Was the database seeded?');

            return 0; // Does not happen
        }

        return $id;
    }

    /**
     * Formats Vehicle Numbers
     *
     * @param string|int|float $number
     *
     * @return string
     */
    private function formatNum($number): string
    {
        return number_format((float)$number, 2, '.', '');
    }

    /**
     * Syncs Vehicle Foci IDs to the Model and generates a Changelog if the Focus has changed
     *
     * @param Vehicle $vehicle
     *
     * @return array Changed vehicle focis
     */
    private function syncFociIds(Vehicle $vehicle): array
    {
        $focus = new Focus($this->rawData);

        return $vehicle->foci()->sync($focus->getVehicleFociIDs());
    }

    /**
     * Syncs Vehicle Component IDs to the Model and generates a Changelog if the Focus has changed
     *
     * @param Vehicle $vehicle
     *
     * @return array Changed components
     */
    private function syncComponents(Vehicle $vehicle): array
    {
        $component = new Component($this->rawData);

        return $vehicle->components()
            ->sync(
                collect($component->getComponents())
                    ->mapWithKeys(
                        function (array $data) {
                            return [
                                $data['component']->id => $data['data'],
                            ];
                        }
                    )
                    ->toArray()
            );
    }
}
