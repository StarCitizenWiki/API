<?php
/**
 * User: Hannes
 * Date: 04.03.2017
 * Time: 12:28
 */

namespace App\Transformers\StarCitizenWiki;

use App\Transformers\BaseAPITransformer;

class ShipsTransformer extends BaseAPITransformer
{
    public function transform($ship)
    {
        $ship['displaytitle'] = str_replace(' ', '_', $ship['displaytitle']);
        return [
            $ship['displaytitle'] => [
                'api_url' => '//'.API_DOMAIN.'/api/v1/ships/'.$ship['displaytitle'],
                'wiki_url' => $ship['fullurl']
            ]
        ];
    }
}