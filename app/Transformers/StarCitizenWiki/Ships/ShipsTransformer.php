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
        $hardpoints = [];
        $wiki = $ship['wiki'];
        $scdb = $ship['scdb'];

        foreach ($wiki['data'] as $key => $item) {
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
            last(explode('/', $wiki['subject'])) ?? $wiki['subject'] => [
                'ship' => [
                    'name' => last(explode('/', $wiki['subject'])) ?? $wiki['subject'],
                    'wiki_url' => BaseStarCitizenWikiAPI::URL.$wiki['subject'],
                ],
                'manufacturer' => [
                    'name' => last(explode('/', $wiki['data']['Hersteller'][0])) ?? $scdb['manufacturer'] ?? '',
                    'id' => explode('/', $wiki['subject'])[1],
                    'wiki_url' => BaseStarCitizenWikiAPI::URL.$wiki['data']['Hersteller'][0],
                ],
                'description' => [
                    'wiki' => $wiki['data']['Beschreibung'][0] ?? '',
                    'game_data' => $scdb['description'] ?? '',
                ],
                'focus' => $wiki['data']['Fokus'] ?? '',
                'status' => $scdb['stats']['status'] ?? snake_case($wiki['data']['Status'][0]) ?? '',
                'price' => $wiki['data']['Schiffspreis'][0] ?? '',
                'dimensions' => [
                    'length' => $wiki['data']['Länge'][0] ?? '',
                    'beam' => $wiki['data']['Breite'][0] ?? '',
                    'height' => $wiki['data']['Höhe'][0] ?? '',
                    'size' => $scdb['stats']['size'] ?? '',
                ],
                'mass' => $scdb['mass'] ?? '',
                'crew' => [
                    'max' => $wiki['data']['Besatzung'][0] ?? '',
                ],
                'freight_capacity' => [
                    'scu' => $wiki['data']['SCU'][0] ?? '',
                ],
                'hit_points' => [
                    'total' => $scdb['stats']['total_hit_points'] ?? '',
                ],
                'components' => [
                    'thruster' => [
                        'main' => [
                            'count' => $wiki['data']['Triebwerk'][0]['Anzahl'] ?? '',
                            'size' => $wiki['data']['Triebwerk'][1]['Größe'] ?? '',
                            'velocity' => $scdb['velocity'] ?? '',
                        ],
                        'maneuvering' => [
                            'count' => $wiki['data']['Steuerdüse'][0]['Anzahl'] ?? '',
                            'size' => $wiki['data']['Steuerdüse'][1]['Größe'] ?? '',
                            'rotation' => $scdb['rotation'] ?? '',
                        ],
                    ],
                    'engine' => [
                        'count' => $wiki['data']['Generator'][0]['Anzahl'] ?? '',
                        'size' => $wiki['data']['Generator'][1]['Größe'] ?? '',
                    ],
                    'shield' => [
                        'count' => $wiki['data']['Schild'][0]['Anzahl'] ?? '',
                        'size' => $wiki['data']['Schild'][1]['Größe'] ?? '',
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
