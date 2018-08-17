<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 24.07.2018
 * Time: 13:59
 */

namespace App\Transformers\Api\V1\StarCitizen\Vehicle\Ship;

use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use App\Transformers\Api\V1\StarCitizen\Vehicle\AbstractVehicleTransformer as VehicleTransformer;

/**
 * Class Wiki Ship Transformer
 * Flat Array
 */
class WikiShipTransformer extends VehicleTransformer
{
    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\Ship\Ship $ship
     *
     * @return array
     */
    public function transform(Ship $ship)
    {
        $merge = [];
        $trim = function ($value) {
            return rtrim($value, ',');
        };

        $foci = $this->getFociTranslations($ship);
        $productionStatuses = $this->getProductionStatusTranslations($ship);
        $productionNotes = $this->getProductionNoteTranslations($ship);
        $types = $this->getTypeTranslations($ship);
        $descriptions = $this->getDescriptionTranslations($ship);
        $sizes = $this->getSizeTranslations($ship);

        $focusMerge = [];
        $multipleLangs = false;
        foreach ($foci as $focus) {
            if (is_array($focus)) {
                $multipleLangs = true;
                foreach ($focus as $lang => $focus1) {
                    $focusMerge[$lang][] = $focus1;
                }
            } else {
                $focusMerge[] = $focus;
            }

        }

        if ($multipleLangs) {
            foreach ($focusMerge as $lang => $focus) {
                $merge["foci_{$lang}"] = implode(',', $focus);
            }
        } else {
            $merge['foci'] = implode(',', $focusMerge);
        }

        if (is_array($productionStatuses)) {
            foreach ($productionStatuses as $lang => $productionStatus) {
                $merge["production_status_{$lang}"] = $productionStatus;
            }
        } else {
            $merge['production_status'] = $productionStatuses;
        }

        if (is_array($productionNotes)) {
            foreach ($productionNotes as $lang => $productionNote) {
                $merge["production_note_{$lang}"] = $productionNote;
            }
        } else {
            $merge['production_note'] = $productionNotes;
        }

        if (is_array($types)) {
            foreach ($types as $lang => $type) {
                $merge["type_{$lang}"] = $type;
            }
        } else {
            $merge['type'] = $types;
        }

        if (is_array($descriptions)) {
            foreach ($descriptions as $lang => $description) {
                $merge["description_{$lang}"] = $description;
            }
        } else {
            $merge['production_note'] = $descriptions;
        }

        if (is_array($sizes)) {
            foreach ($sizes as $lang => $size) {
                $merge["size_{$lang}"] = $size;
            }
        } else {
            $merge['size'] = $sizes;
        }

        $merge = array_map($trim, $merge);

        $data = [
            'id' => $ship->cig_id,
            'chassis_id' => $ship->chassis_id,
            'name' => $ship->name,
            'length' => $ship->length,
            'beam' => $ship->beam,
            'height' => $ship->height,
            'mass' => $ship->mass,
            'cargo_capacity' => $ship->cargo_capacity,
            'crew_min' => $ship->min_crew,
            'crew_max' => $ship->max_crew,
            'scm_speed' => $ship->scm_speed,
            'afterburner_speed' => $ship->afterburner_speed,
            'pitch' => $ship->pitch_max,
            'yaw' => $ship->yaw_max,
            'roll' => $ship->roll_max,
            'x_axis_acceleration' => $ship->x_axis_acceleration,
            'y_axis_acceleration' => $ship->y_axis_acceleration,
            'z_axis_acceleration' => $ship->z_axis_acceleration,
            'manufacturer_code' => $ship->manufacturer->name_short,
            'manufacturer_name' => $ship->manufacturer->name,
        ];

        return array_merge($data, $merge);
    }
}
