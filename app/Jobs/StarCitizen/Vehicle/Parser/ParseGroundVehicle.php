<?php declare(strict_types = 1);

namespace App\Jobs\StarCitizen\Vehicle\Parser;

use App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class ParseShip
 */
class ParseGroundVehicle extends AbstractParseVehicle
{
    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Support\Collection $ship
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
        app('Log')::info("Parsing Ground Vehicle {$this->rawData->get(self::VEHICLE_NAME)}");
        try {
            $ship = Ship::where('name', $this->rawData->get(self::VEHICLE_NAME))->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug('Ground Vehicle not found in DB');
            $this->createNewGroundVehicle();

            return;
        }

        app('Log')::debug('Ground Vehicle found in DB');
        $this->updateGroundVehicle($ship);
    }

    /**
     * Creates a new Ground Vehicle Model
     */
    private function createNewGroundVehicle()
    {
        app('Log')::debug('Creating new Ground Vehicle');

        /** @var \App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle $groundVehicle */
        $groundVehicle = GroundVehicle::create(
            [
                'cig_id' => $this->rawData->get(self::VEHICLE_ID),
                'name' => $this->rawData->get(self::VEHICLE_NAME),
                'manufacturer_id' => $this->getManufacturer()->id,
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
                'chassis_id' => $this->rawData->get(self::VEHICLE_CHASSIS_ID),
            ]
        );

        $groundVehicle->description()->create(
            [
                'language_id' => self::LANGUAGE_EN,
                'translation' => $this->rawData->get(self::VEHICLE_DESCRIPTION),
            ]
        );

        $groundVehicle->foci()->sync($this->getVehicleFociIDs());

        $groundVehicle->setUpdatedAt($this->rawData->get(self::TIME_MODIFIED_UNFILTERED))->save();

        app('Log')::debug('Ground Vehicle created in DB');
    }

    /**
     * Updates a given Ground Vehicle Model
     *
     * @param \App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle $groundVehicle
     */
    private function updateGroundVehicle(GroundVehicle $groundVehicle)
    {
        app('Log')::debug('Updating Ground Vehicle');
        /** @var \Carbon\Carbon $updated */
        $updatedAt = Carbon::createFromTimeString($this->rawData->get(self::TIME_MODIFIED_UNFILTERED));

        if ($updatedAt->equalTo($groundVehicle->updated_at)) {
            app('Log')::debug('Ground Vehicle modified timestamp not changed, not updating');

            return;
        }
    }
}
