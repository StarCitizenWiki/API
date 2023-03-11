<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizenUnpacked;

use App\Http\Resources\AbstractBaseResource;
use App\Models\StarCitizenUnpacked\Vehicle;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\Shop\ShopTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use League\Fractal\Resource\Collection;

/**
 * Class Unpacked Vehicle Transformer
 */
class VehicleTransformer extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'shops',
            'shops.items',
            'hardpoints',
        ];
    }

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
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $name = explode('_', $this->class_name);
        $manufacturer = null;
        $manufacturerCode = null;

        if (isset($this->manufacturers[$name[0]])) {
            $manufacturerCode = $name[0];
            $manufacturer = $this->manufacturers[$name[0]];
        }

        $name = explode(' ', $this->name);
        array_shift($name);
        $name = implode(' ', $name);

        $cargo = $this->cargo_capacity;
        if ($this->SCU > 0) {
            $cargo = $this->scu;
        }

        $data = [
            'id' => $this->vehicle->cig_id ?? null,
            'uuid' => $this->uuid,
            'chassis_id' => $this->vehicle->chassis_id ?? null,
            'name' => $name,
            'slug' => $this->vehicle->slug ?? Str::slug($name),
            'sizes' => [
                'length' => (double)$this->length,
                'beam' => (double)$this->width,
                'height' => (double)$this->height,
            ],
            'mass' => $this->mass,
            'cargo_capacity' => $cargo,
            'personal_inventory_capacity' => $this->personal_inventory_scu ?? null,
            'crew' => [
                'min' => $this->crew,
                'max' => $this->crew,
                'weapon' => $this->weapon_crew?? null,
                'operation' => $this->operation_crew?? null,
            ],
            'health' => $this->health_body?? null,
            'speed' => [
                'scm' => $this->scm_speed,
                'afterburner' => $this->max_speed,
                'max' => $this->max_speed?? null,
                'zero_to_scm' => $this->zero_to_scm?? null,
                'zero_to_max' => $this->zero_to_max?? null,
                'scm_to_zero' => $this->scm_to_zero?? null,
                'max_to_zero' => $this->max_to_zero?? null,
            ],
            'fuel' => [
                'capacity' => $this->fuel_capacity?? null,
                'intake_rate' => $this->fuel_intake_rate?? null,
                'usage' => [
                    'main' => $this->fuel_usage_main?? null,
                    'retro' => $this->fuel_usage_retro?? null,
                    'vtol' => $this->fuel_usage_vtol?? null,
                    'maneuvering' => $this->fuel_usage_maneuvering?? null,
                ],
            ],
            'quantum' => [
                'quantum_speed' => $this->quantum_speed?? null,
                'quantum_spool_time' => $this->quantum_spool_time?? null,
                'quantum_fuel_capacity' => $this->quantum_fuel_capacity?? null,
                'quantum_range' => $this->quantum_range?? null,
            ],
            'agility' => [
                'pitch' => $this->unpacked->pitch?? null,
                'yaw' => $this->unpacked->yaw?? null,
                'roll' => $this->unpacked->roll?? null,
                'acceleration' => [
                    'x_axis' => null,
                    'y_axis' => null,
                    'z_axis' => null,

                    'main' => $this->acceleration_main?? null,
                    'retro' => $this->acceleration_retro?? null,
                    'vtol' => $this->acceleration_vtol?? null,
                    'maneuvering' => $this->acceleration_maneuvering?? null,

                    'main_g' => $this->acceleration_g_main?? null,
                    'retro_g' => $this->acceleration_g_retro?? null,
                    'vtol_g' => $this->acceleration_g_vtol?? null,
                    'maneuvering_g' => $this->acceleration_g_maneuvering?? null,
                ],
            ],
            'foci' => $this->getMappedTranslation($this->role, $this->roles),
            'size' => $this->size,
            'type' => $this->getMappedTranslation($this->career, $this->careers),
            'production_status' => null,
            'production_note' => null,
            'description' => null,

            'msrp' => null,
            'manufacturer' => [
                'code' => $manufacturerCode,
                'name' => $manufacturer,
            ],
            'insurance' => [
                'claim_time' => $this->claim_time?? null,
                'expedite_time' => $this->expedite_time?? null,
                'expedite_cost' => $this->expedite_cost?? null,
            ],
            'updated_at' => $this->updated_at,
        ];

        if (optional($this)->quantum_speed !== null) {
            $data['version'] = config('api.sc_data_version');
        }

        if ($this->vehicle !== null) {
            $transformer = new \App\Transformers\Api\V1\StarCitizen\Vehicle\VehicleTransformer();
            $transformed = $transformer->transform($this->vehicle);
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

    public function includeHardpoints(Vehicle $this): Collection
    {
        $hardpoints = $this->collection($this->hardpointsWithoutParent, new VehicleHardpointTransformer());
        $hardpoints->setMetaValue('info', 'Game Data Components');

        return $hardpoints;
    }

    /**
     * @param Vehicle $this
     * @return Collection
     */
    public function includeShops(Vehicle $this): Collection
    {
        return $this->collection($this->shops, new ShopTransformer());
    }
}
