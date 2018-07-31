<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser;

use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use Tightenco\Collect\Support\Collection;

/**
 * Class ParseShip
 */
class ParseShip extends AbstractParseVehicle
{
    protected const SHIP_PITCH_MAX = 'pitch_max';
    protected const SHIP_YAW_MAX = 'yaw_max';
    protected const SHIP_ROLL_MAX = 'roll_max';
    protected const SHIP_X_AXIS_ACCELERATION = 'xaxis_acceleration';
    protected const SHIP_Y_AXIS_ACCELERATION = 'yaxis_acceleration';
    protected const SHIP_Z_AXIS_ACCELERATION = 'zaxis_acceleration';

    /**
     * Create a new job instance.
     *
     * @param \Tightenco\Collect\Support\Collection $ship
     */
    public function __construct(Collection $ship)
    {
        $this->rawData = $ship;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info("Parsing Ship {$this->rawData->get(self::VEHICLE_NAME)}");

        /** @var \App\Models\Api\StarCitizen\Vehicle\Ship\Ship $ship */
        $ship = Ship::updateOrCreate(
            [
                'cig_id' => (int) $this->rawData->get(self::VEHICLE_ID),
            ],
            [
                'cig_id' => (int) $this->rawData->get(self::VEHICLE_ID),
                'name' => (string) $this->rawData->get(self::VEHICLE_NAME),
                'manufacturer_id' => (int) $this->getManufacturer()->cig_id,
                'production_status_id' => (int) $this->getProductionStatus()->id,
                'production_note_id' => (int) $this->getProductionNote()->id,
                'vehicle_size_id' => (int) $this->getVehicleSize()->id,
                'vehicle_type_id' => (int) $this->getVehicleType()->id,
                'length' => (float) $this->rawData->get(self::VEHICLE_LENGTH),
                'beam' => (float) $this->rawData->get(self::VEHICLE_BEAM),
                'height' => (float) $this->rawData->get(self::VEHICLE_HEIGHT),
                'mass' => (int) $this->rawData->get(self::VEHICLE_MASS),
                'cargo_capacity' => (int) $this->rawData->get(self::VEHICLE_CARGO_CAPACITY),
                'min_crew' => (int) $this->rawData->get(self::VEHICLE_MIN_CREW),
                'max_crew' => (int) $this->rawData->get(self::VEHICLE_MAX_CREW),
                'scm_speed' => (int) $this->rawData->get(self::VEHICLE_SCM_SPEED),
                'afterburner_speed' => (int) $this->rawData->get(self::VEHICLE_AFTERBURNER_SPEED),
                'pitch_max' => (float) $this->rawData->get(self::SHIP_PITCH_MAX),
                'yaw_max' => (float) $this->rawData->get(self::SHIP_YAW_MAX),
                'roll_max' => (float) $this->rawData->get(self::SHIP_ROLL_MAX),
                'x_axis_acceleration' => (float) $this->rawData->get(self::SHIP_X_AXIS_ACCELERATION),
                'y_axis_acceleration' => (float) $this->rawData->get(self::SHIP_Y_AXIS_ACCELERATION),
                'z_axis_acceleration' => (float) $this->rawData->get(self::SHIP_Z_AXIS_ACCELERATION),
                'chassis_id' => (int) $this->rawData->get(self::VEHICLE_CHASSIS_ID),
                'updated_at' => $this->rawData->get(self::TIME_MODIFIED_UNFILTERED),
            ]
        );

        $ship->translations()->updateOrCreate(
            [
                'ship_id' => $ship->id,
                'locale_code' => self::LANGUAGE_EN,
            ],
            [
                'translation' => $this->rawData->get(self::VEHICLE_DESCRIPTION),
            ]
        );

        $fociIDsOld = [];
        $fociIDs = $this->getVehicleFociIDs();

        foreach ($ship->foci as $focus) {
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

            $ship->changelogs()->create(
                [
                    'changelog' => json_encode($changes),
                ]
            );

            app('Log')::debug('Updated ship_vehicle_focus', $changes);

            $ship->foci()->sync($this->getVehicleFociIDs());
        }
    }
}
