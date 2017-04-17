<?php
/**
 * User: Hannes
 * Date: 04.03.2017
 * Time: 12:28
 */

namespace App\Transformers\StarCitizenWiki\Ships;

use App\Repositories\StarCitizen\APIv1\BaseStarCitizenAPI;
use App\Repositories\StarCitizenWiki\APIv1\BaseStarCitizenWikiAPI;
use App\Traits\FiltersDataTrait;
use App\Transformers\BaseAPITransformerInterface;
use GuzzleHttp\Client;
use League\Fractal\TransformerAbstract;

/**
 * Class ShipsTransformer
 *
 * @package App\Transformers\StarCitizenWiki\Ships
 */
class ShipsTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
    use FiltersDataTrait;

    /**
     * Transformes a given Ship
     *
     * @param mixed $ship Ship to transform
     *
     * @return mixed
     */
    public function transform($ship)
    {
    //    dump($ship);

        $hardpoints = [];

        foreach ($ship['wiki'] as $key => $item) {
            if (starts_with($key, 'Hardpoint')) {
                $name = snake_case(str_replace('Hardpoint_', '', $key));
                $hardpoints += [
                    $name => [
                        'count' => $item[0]['Anzahl'] ?? '',
                        'size' => $item[1]['Größe'] ?? '',
                    ],
                ];
            }
        }

        $transformed = [
            $ship['wiki']['smw']['page_title'][0] ?? $ship['wiki']['subject'] => [
                'ship' => [
                    'name' => $ship['wiki']['smw']['page_title'][0] ?? $ship['wiki']['subject'],
                    'wiki_url' => BaseStarCitizenWikiAPI::URL.str_replace('#0#', '', $ship['wiki']['subject']),
                ],
                'manufacturer' => [
                    'name' => str_replace('#0#', '', last(explode('/', $ship['wiki']['Hersteller'][0]))) ?? $ship['scdb']['manufacturer'] ??'',
                    'id' => explode('/', $ship['wiki']['subject'])[1],
                    'wiki_url' => BaseStarCitizenWikiAPI::URL.str_replace('#0#', '', $ship['wiki']['Hersteller'][0]),
                ],
                'description' => [
                    'wiki' => $ship['wiki']['Beschreibung'][0] ?? '',
                    'game_data' => $ship['scdb']['description'] ?? '',
                ],
                'focus' => $ship['wiki']['Fokus'] ?? '',
                'status' => $ship['scdb']['stats']['status'] ?? snake_case($ship['wiki']['Status'][0]) ?? '',
                'price' => $ship['wiki']['Schiffspreis'][0] ?? '',
                'dimensions' => [
                    'length' => $ship['wiki']['Länge'][0] ?? '',
                    'beam' => $ship['wiki']['Breite'][0] ?? '',
                    'height' => $ship['wiki']['Höhe'][0] ?? '',
                    'size' => $ship['scdb']['stats']['size'] ?? '',
                ],
                'mass' => $ship['scdb']['mass'] ?? '',
                'crew' => [
                    'max' => $ship['wiki']['Besatzung'][0] ?? '',
                ],
                'freight_capacity' => [
                    'scu' => $ship['wiki']['SCU'][0] ?? '',
                ],
                'hit_points' => [
                    'total' => $ship['scdb']['stats']['total_hit_points'] ?? '',
                ],
                'components' => [
                    'thruster' => [
                        'main' => [
                            'count' => $ship['wiki']['Triebwerk'][0]['Anzahl'] ?? '',
                            'size' => $ship['wiki']['Triebwerk'][1]['Größe'] ?? '',
                            'velocity' => $ship['scdb']['velocity'] ?? '',
                        ],
                        'maneuvering' => [
                            'count' => $ship['wiki']['Steuerdüse'][0]['Anzahl'] ?? '',
                            'size' => $ship['wiki']['Steuerdüse'][1]['Größe'] ?? '',
                            'rotation' => $ship['scdb']['rotation'] ?? '',
                        ],
                    ],
                    'engine' => [
                        'count' => $ship['wiki']['Generator'][0]['Anzahl'] ?? '',
                        'size' => $ship['wiki']['Generator'][1]['Größe'] ?? '',
                    ],
                    'shield' => [
                        'count' => $ship['wiki']['Schild'][0]['Anzahl'] ?? '',
                        'size' => $ship['wiki']['Schild'][1]['Größe'] ?? '',
                    ],
                    'hardpoint' => [
                        $hardpoints,
                    ],
                ],
            ],
        ];



        return $this->filterData($transformed);
    }
}
