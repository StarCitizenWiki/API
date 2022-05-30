<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\Vehicle;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\Shop\ShopTransformer;
use Illuminate\Support\Str;
use League\Fractal\Resource\Collection;

/**
 * Class Unpacked Vehicle Transformer
 */
class VehicleTransformer extends AbstractTranslationTransformer
{
    protected $availableIncludes = [
        'shops',
        'hardpoints',
    ];

    private array $manufacturers = [
        'AEGS' => 'Aegis Dynamics',
        'ANVL' => 'Anvil Aerospace',
        'ARGO' => 'Argo Astronautics',
        'BANU' => 'Banu',
        'CNOU' => 'Consolidated Outland',
        'CRUS' => 'Crusader Industries',
        'DRAK' => 'Drake Interplanetary',
        'ESPR' => 'Esperia',
        'GRIN' => 'Greycat Industrial',
        'KRIG' => 'Kruger Intergalactic',
        'MISC' => 'Musashi Industrial & Starflight Concern',
        'ORIG' => 'Origin Jumpworks GmbH',
        'RSI' => 'Roberts Space Industries',
        'TMBL' => 'Tumbril',
        'VNCL' => 'Vanduul',
        'XIAN' => 'Xi\'an',
    ];

    /**
     * TODO: Move to DB
     *
     * @var array|string[]
     */
    private array $roles = [
        'Bomber' => 'Bomber',
        'Combat' => 'Gefecht',
        'Corvette' => 'Korvette',
        'Courier' => 'Kurier',
        'Destroyer' => 'Zerstörer',
        'Drop Ship' => 'Landungsschiff',
        'Expedition' => 'Forschungsreisen',
        'Exploration' => 'Erkundung',
        'Fighter' => 'Kampf',
        'Frigate' => 'Fregatte',
        'Ground' => 'Bodenfahrzeug',
        'Gunship' => 'Kanonenboot',
        'Gun Ship' => 'Kanonenboot',
        'Heavy Bomber' => 'Schwerer Bomber',
        'Heavy Fighter' => 'Schwerer Jäger',
        'Heavy Freight' => 'Schwertransport',
        'Heavy Refuelling' => 'Schwerbetankung',
        'Heavy Salvage' => 'Großbergung',
        'Interceptor' => 'Abfangjäger',
        'Interdiction' => 'Abriegelung',
        'Light Fighter' => 'Leichter Jäger',
        'Light Freight' => 'Leichter Frachter',
        'Light Mining' => 'Leichter Bergbau',
        'Light Science' => 'Einfache Forschung',
        'Luxury' => 'Komfort',
        'Medical' => 'Medizin',
        'Medium Data' => 'Mittlerer Datentransport',
        'Medium Fighter' => 'Mittlerer Jäger',
        'Medium Freight' => 'Mittlerer Frachter',
        'Medium Mining' => 'Mittlerer Bergbau',
        'Passenger' => 'Passagier',
        'Pathfinder' => 'Pfadfinder',
        'Racing' => 'Rennsport',
        'Reporting' => 'Berichterstattung',
        'Snub Fighter' => 'Beiboot Jäger',
        'Starter' => 'Einsteiger',
        'Stealth Bomber' => 'Tarnkappenbomber',
        'Stealth Fighter' => 'Tarnkappenjäger',
        'Support' => 'Unterstützung',
        'Transporter' => 'Transporter',
    ];

    /**
     * TODO: Move to DB
     *
     * @var array|string[]
     */
    private array $careers = [
        'Combat' => 'Gefecht',
        'Transporter' => 'Transport',
        'Industrial' => 'Gewerblich',
        'Exploration' => 'Erkundung',
        'Support' => 'Unterstützung',
        'Multi-Role' => 'Mehrzweck',
        'Competition' => 'Wettkampf',
        'Ground' => 'Bodenfahrzeug',
    ];

    /**
     * @param Vehicle $vehicle
     *
     * @return array
     */
    public function transform(Vehicle $vehicle): array
    {
        $name = explode('_', $vehicle->class_name);
        $manufacturer = null;
        $manufacturerCode = null;

        if (isset($this->manufacturers[$name[0]])) {
            $manufacturerCode = $name[0];
            $manufacturer = $this->manufacturers[$name[0]];
        }

        $name = explode(' ', $vehicle->name);
        array_shift($name);
        $name = implode(' ', $name);

        $cargo = $vehicle->cargo_capacity;
        if ($vehicle->SCU > 0) {
            $cargo = $vehicle->scu;
        }

        $data = [
            'id' => $vehicle->vehicle->cig_id ?? null,
            'uuid' => $vehicle->uuid,
            'chassis_id' => $vehicle->vehicle->chassis_id ?? null,
            'name' => $name,
            'slug' => $vehicle->vehicle->slug ?? Str::slug($name),
            'sizes' => [
                'length' => (double)$vehicle->length,
                'beam' => (double)$vehicle->width,
                'height' => (double)$vehicle->height,
            ],
            'mass' => $vehicle->mass,
            'cargo_capacity' => $cargo,
            'personal_inventory_capacity' => $vehicle->personal_inventory_scu ?? null,
            'crew' => [
                'min' => $vehicle->crew,
                'max' => $vehicle->crew,
                'weapon' => $vehicle->weapon_crew ?? 0,
                'operation' => $vehicle->operation_crew ?? 0,
            ],
            'health' => $vehicle->health_body ?? 0,
            'speed' => [
                'scm' => $vehicle->scm_speed,
                'afterburner' => $vehicle->max_speed,
                'max' => $vehicle->max_speed ?? 0,
                'zero_to_scm' => $vehicle->zero_to_scm ?? 0,
                'zero_to_max' => $vehicle->zero_to_max ?? 0,
                'scm_to_zero' => $vehicle->scm_to_zero ?? 0,
                'max_to_zero' => $vehicle->max_to_zero ?? 0,
            ],
            'fuel' => [
                'capacity' => $vehicle->fuel_capacity ?? 0,
                'intake_rate' => $vehicle->fuel_intake_rate ?? 0,
                'usage' => [
                    'main' => $vehicle->fuel_usage_main ?? 0,
                    'retro' => $vehicle->fuel_usage_retro ?? 0,
                    'vtol' => $vehicle->fuel_usage_vtol ?? 0,
                    'maneuvering' => $vehicle->fuel_usage_maneuvering ?? 0,
                ],
            ],
            'quantum' => [
                'quantum_speed' => $vehicle->quantum_speed ?? 0,
                'quantum_spool_time' => $vehicle->quantum_spool_time ?? 0,
                'quantum_fuel_capacity' => $vehicle->quantum_fuel_capacity ?? 0,
                'quantum_range' => $vehicle->quantum_range ?? 0,
            ],
            'agility' => [
                'pitch' => $vehicle->unpacked->pitch ?? 0,
                'yaw' => $vehicle->unpacked->yaw ?? 0,
                'roll' => $vehicle->unpacked->roll ?? 0,
                'acceleration' => [
                    'x_axis' => null,
                    'y_axis' => null,
                    'z_axis' => null,

                    'main' => $vehicle->acceleration_main ?? 0,
                    'retro' => $vehicle->acceleration_retro ?? 0,
                    'vtol' => $vehicle->acceleration_vtol ?? 0,
                    'maneuvering' => $vehicle->acceleration_maneuvering ?? 0,

                    'main_g' => $vehicle->acceleration_g_main ?? 0,
                    'retro_g' => $vehicle->acceleration_g_retro ?? 0,
                    'vtol_g' => $vehicle->acceleration_g_vtol ?? 0,
                    'maneuvering_g' => $vehicle->acceleration_g_maneuvering ?? 0,
                ],
            ],
            'foci' => $this->getMappedTranslation($vehicle->role, $this->roles),
            'size' => $vehicle->size,
            'type' => $this->getMappedTranslation($vehicle->career, $this->careers),
            'production_status' => null,
            'production_note' => null,
            'description' => null,

            'msrp' => null,
            'manufacturer' => [
                'code' => $manufacturerCode,
                'name' => $manufacturer,
            ],
            'insurance' => [
                'claim_time' => $vehicle->claim_time ?? 0,
                'expedite_time' => $vehicle->expedite_time ?? 0,
                'expedite_cost' => $vehicle->expedite_cost ?? 0,
            ],
            'updated_at' => $vehicle->updated_at,
        ];

        if (optional($vehicle)->quantum_speed !== null) {
            $data['version'] = config('api.sc_data_version');
        }

        if ($vehicle->vehicle !== null) {
            $transformer = new \App\Transformers\Api\V1\StarCitizen\Vehicle\VehicleTransformer();
            $transformed = $transformer->transform($vehicle->vehicle);
            $data['foci'] = $transformed['foci'];
            $data['production_status'] = $transformed['production_status'];
            $data['production_note'] = $transformed['production_note'];
            $data['type'] = $transformed['type'];
            $data['description'] = $transformed['description'];
            $data['size'] = $transformed['size'];
        }

        return $data;
    }

    private function getMappedTranslation($role, array $data)
    {
        $out = collect(explode('/', $role))
            ->map(function ($role) use ($data) {
                $role = trim($role);
                return [
                    'en_EN' => $role,
                    'de_DE' => $data[$role] ?? $role,
                ];
            });

        if (isset($this->localeCode)) {
            $out = $out->map(function ($entry) {
                return $entry[$this->localeCode];
            });
        }

        return $out;
    }

    public function includeHardpoints(Vehicle $vehicle): Collection
    {
        $hardpoints = $this->collection($vehicle->hardpointsWithoutParent, new VehicleHardpointTransformer());
        $hardpoints->setMetaValue('info', 'Game Data Components');

        return $hardpoints;
    }

    /**
     * @param Vehicle $vehicle
     * @return Collection
     */
    public function includeShops(Vehicle $vehicle): Collection
    {
        return $this->collection($vehicle->shops, new ShopTransformer());
    }
}
