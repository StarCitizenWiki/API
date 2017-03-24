<?php
/**
 * User: Hannes
 * Date: 04.03.2017
 * Time: 12:28
 */

namespace App\Transformers\StarCitizenWiki\Ships;

use App\Transformers\BaseAPITransformerInterface;
use League\Fractal\TransformerAbstract;

/**
 * Class ShipsListTransformer
 *
 * @package App\Transformers\StarCitizenWiki\Ships
 */
class ShipsListTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
    /**
     * Transformes the whole ship list
     *
     * @param mixed $ship Data
     *
     * @return array
     */
    public function transform($ship)
    {
        $ship['displaytitle'] = str_replace(' ', '_', $ship['displaytitle']);
        return [
            $ship['displaytitle'] => [
                'api_url' => '//'.config('app.api_domain').'/api/v1/ships/'.$ship['displaytitle'],
                'wiki_url' => $ship['fullurl']
            ]
        ];
    }
}