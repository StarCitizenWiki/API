<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 24.07.2018
 * Time: 13:59
 */

namespace App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle;

use App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use App\Transformers\Api\V1\StarCitizen\Vehicle\AbstractVehicleTransformer as VehicleTransformer;

/**
 * Class Wiki Ground Vehicle Transformer
 * Flat Array
 */
class WikiGroundVehicleTransformer extends VehicleTransformer
{
    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle $groundVehicle
     *
     * @return array
     */
    public function transform(GroundVehicle $groundVehicle)
    {
        $merge = [];
        $trim = function ($value) {
            return rtrim($value, ',');
        };

        $foci = $this->getFociTranslations($groundVehicle);
        $productionStatuses = $this->getProductionStatusTranslations($groundVehicle);
        $productionNotes = $this->getProductionNoteTranslations($groundVehicle);
        $types = $this->getTypeTranslations($groundVehicle);
        $descriptions = $this->getDescriptionTranslations($groundVehicle);
        $sizes = $this->getSizeTranslations($groundVehicle);

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
            'id' => $groundVehicle->cig_id,
            'chassis_id' => $groundVehicle->chassis_id,
            'name' => $groundVehicle->name,
            'length' => $groundVehicle->length,
            'beam' => $groundVehicle->beam,
            'height' => $groundVehicle->height,
            'mass' => $groundVehicle->mass,
            'cargo_capacity' => $groundVehicle->cargo_capacity,
            'crew_min' => $groundVehicle->min_crew,
            'crew_max' => $groundVehicle->max_crew,
            'scm_speed' => $groundVehicle->scm_speed,
            'manufacturer_code' => $groundVehicle->manufacturer->name_short,
            'manufacturer_name' => $groundVehicle->manufacturer->name,
        ];

        return array_merge($data, $merge);
    }
}
