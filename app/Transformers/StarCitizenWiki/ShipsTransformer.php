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
        return $ship;
    }
}