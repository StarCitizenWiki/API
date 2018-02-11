<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 04.03.2017
 * Time: 12:28
 */

namespace App\Transformers\StarCitizenWiki\Ships;

use App\Transformers\AbstractBaseTransformer;

/**
 * Class ShipsListTransformer
 */
class ShipsListTransformer extends AbstractBaseTransformer
{
    protected $validFields = [
        'wiki_url',
        'api_url',
    ];

    /**
     * Transforms the whole ship list
     *
     * @param mixed $ship Data
     *
     * @return array
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function transform($ship)
    {
        $ship['displaytitle'] = str_replace(' ', '_', $ship['displaytitle']);

        $transformed = [
            $ship['displaytitle'] => [
                'api_url' => config('app.api_url').'/api/v1/ships/'.$ship['displaytitle'],
                'wiki_url' => $ship['fullurl'],
            ],
        ];

        return $this->filterData($transformed);
    }
}
