<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.03.2017
 * Time: 20:04
 */

namespace App\Transformers\StarCitizen\Starmap;

use App\Transformers\BaseAPITransformerInterface;
use League\Fractal\TransformerAbstract;

/**
 * Class SystemTransformer
 *
 * @package App\Transformers\StarCitizen\Starmap
 */
class AsteroidbeltsTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
    private const OBJECT_TYPE = 'ASTEROID_BELT';

    /**
     * Returns all Asteroidbelts of the System Data
     *
     * @param mixed $system System Data
     *
     * @return mixed
     */
    public function transform($system)
    {
        $transformed = [
            'success'       => $system['success'],
            'rowcount'      => $system['data']['rowcount'],
            'totalrows'     => $system['data']['totalrows'],
            'estimatedrows' => $system['data']['estimatedrows'],
            'pagesize'      => $system['data']['pagesize'],
            'pagecount'     => $system['data']['pagecount'],
            'page'          => $system['data']['page'],
            'offset'        => $system['data']['offset'],
            'startrow'      => $system['data']['startrow'],
        ];

        $asteroidbelts = [];
        $celestialObjects = $system['data']['resultset'][0]['celestial_objects'];
        foreach ($celestialObjects as $celestialObject) {
            if (strcmp($celestialObject['type'], self::OBJECT_TYPE) == 0) {
                $asteroidbelts[] = $celestialObject;
            }
        }

        $transformedAsteroidbelts = [
            'resultset' => $asteroidbelts,
        ];

        $codeMessage = [
            'code' => $system['code'],
            'msg'  => $system['msg'],
        ];

        return array_merge($transformed, $transformedAsteroidbelts, $codeMessage);
    }
}
