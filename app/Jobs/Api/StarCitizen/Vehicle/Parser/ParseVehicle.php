<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser;

use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\Manufacturer;
use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\ProductionNote;
use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\ProductionStatus;
use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\Vehicle\Focus;
use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\Vehicle\Size;
use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\Vehicle\Type;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        app('Log')::info(
            "Parsing Vehicle {$this->rawData->get(self::VEHICLE_NAME)}",
            [
                'vehicle' => $this->rawData->get(self::VEHICLE_NAME),
            ]
        );

        /** @var \App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle $vehicle */
        $vehicle = Vehicle::query()->updateOrCreate(
            [
                'cig_id' => $this->getId(),
            ],
            [
                'name' => $this->getName(),
                'slug' => $this->getSlug(),
                'manufacturer_id' => $this->getManufacturerId(),
                'production_status_id' => $this->getProductionStatusId(),
                'production_note_id' => $this->getProductionNoteId(),
                'size_id' => $this->getSizeId(),
                'type_id' => $this->getTypeId(),
                'length' => $this->getLength(),
                'beam' => $this->getBeam(),
                'height' => $this->getHeight(),
                'mass' => $this->getMass(),
                'cargo_capacity' => $this->getCargoCapacity(),
                'min_crew' => $this->getMinCrew(),
                'max_crew' => $this->getMaxCrew(),
                'scm_speed' => $this->getScmSpeed(),
                'afterburner_speed' => $this->getAfterburnerSpeed(),
                'pitch_max' => $this->getPitchMax(),
                'yaw_max' => $this->getYawMax(),
                'roll_max' => $this->getRollMax(),
                'x_axis_acceleration' => $this->getXAxisAcceleration(),
                'y_axis_acceleration' => $this->getYAxisAcceleration(),
                'z_axis_acceleration' => $this->getZAxisAcceleration(),
                'chassis_id' => $this->getChassisId(),
                'updated_at' => $this->getUpdatedAt(),
            ]
        );

        $vehicle->translations()->updateOrCreate(
            [
                'locale_code' => config('language.english'),
            ],
            [
                'translation' => $this->getDescription(),
            ]
        );

        $this->syncFociIds($vehicle);
    }

    /**
     * Syncs Vehicle Foci IDs to the Model and generates a Changelog if the Focus has changed
     *
     * @param \App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle $vehicle
     */
    private function syncFociIds(Vehicle $vehicle)
    {
        $focus = new Focus($this->rawData);

        /** @var \Illuminate\Database\Eloquent\Collection $fociIDsOld */
        $fociIDsOld = $vehicle->foci->pluck('id');
        $fociIDs = $focus->getVehicleFociIDs();

        if (!$vehicle->wasRecentlyCreated && count($fociIDsOld->diff($fociIDs)) > 0) {
            $changes = [
                'foci' => [
                    'old' => $fociIDsOld,
                    'new' => $fociIDs,
                ],
            ];

            $vehicle->changelogs()->create(
                [
                    'type' => 'update',
                    'user_id' => 0,
                    'changelog' => json_encode($changes),
                ]
            );

            app('Log')::debug('Updated ship_vehicle_focus', $changes);
        }

        $vehicle->foci()->sync($fociIDs);
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
        return number_format((float) $number, 2, '.', '');
    }

    /**
     * @return string
     */
    private function getName(): string
    {
        return $this->rawData->get(self::VEHICLE_NAME);
    }

    /**
     * @return string
     */
    private function getSlug(): string
    {
        return str_slug($this->getName());
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

        return $productionStatus->getProductionStatus()->id;
    }

    /**
     * @return int
     */
    private function getProductionNoteId(): int
    {
        $productionNote = new ProductionNote($this->rawData);

        return $productionNote->getProductionNote()->id;
    }

    /**
     * @return int
     */
    private function getSizeId(): int
    {
        $size = new Size($this->rawData);

        return $size->getVehicleSize()->id;
    }

    /**
     * @return int
     */
    private function getTypeId(): int
    {
        $type = new Type($this->rawData);

        return $type->getVehicleType()->id;
    }

    /**
     * @return string
     */
    private function getLength(): string
    {
        return $this->formatNum($this->rawData->get(self::VEHICLE_LENGTH));
    }

    /**
     * @return string
     */
    private function getBeam(): string
    {
        return $this->formatNum($this->rawData->get(self::VEHICLE_BEAM));
    }

    /**
     * @return string
     */
    private function getHeight(): string
    {
        return $this->formatNum($this->rawData->get(self::VEHICLE_HEIGHT));
    }

    /**
     * @return int
     */
    private function getMass(): int
    {
        return (int) $this->rawData->get(self::VEHICLE_MASS);
    }

    /**
     * @return int
     */
    private function getCargoCapacity(): int
    {
        return (int) $this->rawData->get(self::VEHICLE_CARGO_CAPACITY);
    }

    /**
     * @return int
     */
    private function getMinCrew(): int
    {
        return (int) $this->rawData->get(self::VEHICLE_MIN_CREW);
    }

    /**
     * @return int
     */
    private function getMaxCrew(): int
    {
        return (int) $this->rawData->get(self::VEHICLE_MAX_CREW);
    }

    /**
     * @return int
     */
    private function getScmSpeed(): int
    {
        return (int) $this->rawData->get(self::VEHICLE_SCM_SPEED);
    }

    /**
     * @return int
     */
    private function getAfterburnerSpeed(): int
    {
        return (int) $this->rawData->get(self::VEHICLE_AFTERBURNER_SPEED);
    }

    /**
     * @return string
     */
    private function getPitchMax(): string
    {
        return $this->formatNum($this->rawData->get(self::VEHICLE_PITCH_MAX));
    }

    /**
     * @return string
     */
    private function getYawMax(): string
    {
        return $this->formatNum($this->rawData->get(self::VEHICLE_YAW_MAX));
    }

    /**
     * @return string
     */
    private function getRollMax(): string
    {
        return $this->formatNum($this->rawData->get(self::VEHICLE_ROLL_MAX));
    }

    /**
     * @return string
     */
    private function getXAxisAcceleration(): string
    {
        return $this->formatNum($this->rawData->get(self::VEHICLE_X_AXIS_ACCELERATION));
    }

    /**
     * @return string
     */
    private function getYAxisAcceleration(): string
    {
        return $this->formatNum($this->rawData->get(self::VEHICLE_Y_AXIS_ACCELERATION));
    }

    /**
     * @return string
     */
    private function getZAxisAcceleration(): string
    {
        return $this->formatNum($this->rawData->get(self::VEHICLE_Z_AXIS_ACCELERATION));
    }

    /**
     * @return int
     */
    private function getChassisId(): int
    {
        return (int) $this->rawData->get(self::VEHICLE_CHASSIS_ID);
    }

    /**
     * @return string
     */
    private function getUpdatedAt(): string
    {
        return $this->rawData->get(self::TIME_MODIFIED_UNFILTERED);
    }

    /**
     * @return int
     */
    private function getId(): int
    {
        return (int) $this->rawData->get(self::VEHICLE_ID);
    }

    /**
     * @return string|null
     */
    private function getDescription()
    {
        return $this->rawData->get(self::VEHICLE_DESCRIPTION);
    }
}
