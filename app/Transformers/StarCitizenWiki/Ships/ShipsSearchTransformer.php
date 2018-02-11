<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 04.03.2017
 * Time: 12:28
 */

namespace App\Transformers\StarCitizenWiki\Ships;

use App\Exceptions\InvalidDataException;
use App\Transformers\AbstractBaseTransformer;

/**
 * Class ShipsSearchTransformer
 */
class ShipsSearchTransformer extends AbstractBaseTransformer
{
    protected $validFields = [
        'wiki_url',
        'api_url',
    ];

    /**
     * Transforms a ship search query
     *
     * @param mixed $search Ship search data
     *
     * @return array
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function transform($search)
    {
        $search['title'] = str_replace(' ', '_', $search['title']);
        $result = explode('/', $search['title']);
        if (3 === count($result)) {
            $shipName = $result[2];

            $data = [
                $shipName => [
                    'api_url'  => config('app.api_url').'/api/v1/ships/'.$shipName,
                    'wiki_url' => config('api.wiki_url').'/'.$search['title'],
                ],
            ];

            return $this->filterData($data);
        }
        throw new InvalidDataException('result size should be 3, is '.count($result));
    }
}
