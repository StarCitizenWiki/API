<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser;

use App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use Tightenco\Collect\Support\Collection;

/**
 * Class ParseShip
 */
class ParseGroundVehicle extends AbstractParseVehicle
{
    /**
     * Create a new job instance.
     *
     * @param \Tightenco\Collect\Support\Collection $groundVehicle
     */
    public function __construct(Collection $groundVehicle)
    {
        $this->rawData = $groundVehicle;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info("Parsing Ground Vehicle {$this->rawData->get(self::VEHICLE_NAME)}");

        $groundVehicle = GroundVehicle::updateOrCreate(
            [
                'cig_id' => $this->rawData->get(self::VEHICLE_ID),
            ],
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
                'chassis_id' => $this->rawData->get(self::VEHICLE_CHASSIS_ID),
                'updated_at' => $this->rawData->get(self::TIME_MODIFIED_UNFILTERED),
            ]
        );

        $groundVehicle->translations()->updateOrCreate(
            [
                'ground_vehicle_id' => $groundVehicle->id,
                'locale_code' => self::LANGUAGE_EN,
            ],
            [
                'translation' => $this->rawData->get(self::VEHICLE_DESCRIPTION),
            ]
        );

        $fociIDsOld = [];
        $fociIDs = $this->getVehicleFociIDs();

        foreach ($groundVehicle->foci as $focus) {
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

            $groundVehicle->changelogs()->create(
                [
                    'changelog' => json_encode($changes),
                ]
            );

            app('Log')::debug('Updated ground_vehicle_vehicle_focus', $changes);

            $groundVehicle->foci()->sync($this->getVehicleFociIDs());
        }
    }
}
