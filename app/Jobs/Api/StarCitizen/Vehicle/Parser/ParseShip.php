<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser;

use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
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
        try {
            $ship = Ship::where('name', $this->rawData->get(self::VEHICLE_NAME))->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Ship not found in DB');
            $this->createNewShip();

            return;
        }

        app('Log')::debug('Ship found in DB');
        $this->updateShip($ship);
    }

    /**
     * Creates a new Ship Model
     */
    private function createNewShip()
    {
        app('Log')::debug('Creating new Ship');

        /** @var \App\Models\Api\StarCitizen\Vehicle\Ship\Ship $ship */
        $ship = Ship::create(
            [
                'cig_id' => $this->rawData->get(self::VEHICLE_ID),
                'name' => $this->rawData->get(self::VEHICLE_NAME),
                'manufacturer_id' => $this->getManufacturer()->cig_id,
                'production_status_id' => $this->getProductionStatus()->id,
                'production_note_id' => $this->getProductionNote()->id,
                'vehicle_size_id' => $this->getVehicleSize()->id,
                'vehicle_type_id' => $this->getVehicleType()->id,
                'length' => $this->rawData->get(self::VEHICLE_LENGTH),
                'beam' => $this->rawData->get(self::VEHICLE_BEAM),
                'height' => $this->rawData->get(self::VEHICLE_HEIGHT),
                'mass' => $this->rawData->get(self::VEHICLE_MASS),
                'cargo_capacity' => $this->rawData->get(self::VEHICLE_CARGO_CAPACITY),
                'min_crew' => $this->rawData->get(self::VEHICLE_MIN_CREW),
                'max_crew' => $this->rawData->get(self::VEHICLE_MAX_CREW),
                'scm_speed' => $this->rawData->get(self::VEHICLE_SCM_SPEED),
                'afterburner_speed' => $this->rawData->get(self::VEHICLE_AFTERBURNER_SPEED),
                'pitch_max' => $this->rawData->get(self::SHIP_PITCH_MAX),
                'yaw_max' => $this->rawData->get(self::SHIP_YAW_MAX),
                'roll_max' => $this->rawData->get(self::SHIP_ROLL_MAX),
                'x_axis_acceleration' => $this->rawData->get(self::SHIP_X_AXIS_ACCELERATION),
                'y_axis_acceleration' => $this->rawData->get(self::SHIP_Y_AXIS_ACCELERATION),
                'z_axis_acceleration' => $this->rawData->get(self::SHIP_Z_AXIS_ACCELERATION),
                'chassis_id' => $this->rawData->get(self::VEHICLE_CHASSIS_ID),
            ]
        );

        $ship->translations()->create(
            [
                'locale_code' => self::LANGUAGE_EN,
                'translation' => $this->rawData->get(self::VEHICLE_DESCRIPTION),
            ]
        );

        $ship->foci()->sync($this->getVehicleFociIDs());

        $ship->setUpdatedAt($this->rawData->get(self::TIME_MODIFIED_UNFILTERED))->save();

        app('Log')::debug('Ship created in DB');
    }

    /**
     * Updates a given Ship Model
     *
     * @param \App\Models\Api\StarCitizen\Vehicle\Ship\Ship $ship
     */
    private function updateShip(Ship $ship)
    {
        app('Log')::debug('Updating Ship');
        /** @var \Carbon\Carbon $updated */
        $updatedAt = Carbon::createFromTimeString($this->rawData->get(self::TIME_MODIFIED_UNFILTERED));

        if ($updatedAt->equalTo($ship->updated_at)) {
            app('Log')::debug('Ship modified timestamp not changed, not updating');

            return;
        }
    }
}
