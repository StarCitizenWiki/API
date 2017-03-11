<?php
/**
 * User: Hannes
 * Date: 04.03.2017
 * Time: 12:28
 */

namespace App\Transformers\StarCitizenWiki\Ships;

use App\Exceptions\InvalidDataException;
use App\Transformers\BaseAPITransformerInterface;
use League\Fractal\TransformerAbstract;

class ShipsSearchTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
    public function transform($search)
    {
        $search['title'] = str_replace(' ', '_', $search['title']);
        $result = explode('/', $search['title']);
        if (count($result) === 3) {
            $shipName = $result[2];
            return [
                $shipName => [
                    'api_url' => '//'.API_DOMAIN.'/api/v1/ships/'.$shipName,
                    'wiki_url' => '//star-citizen.wiki/'.$search['title']
                ]
            ];
        }

        throw new InvalidDataException('result size should be 3, is '.count($result));
    }
}