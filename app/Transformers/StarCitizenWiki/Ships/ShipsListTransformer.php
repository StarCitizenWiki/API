<?php
/**
 * User: Hannes
 * Date: 04.03.2017
 * Time: 12:28
 */

namespace App\Transformers\StarCitizenWiki\Ships;

use App\Traits\FiltersDataTrait;
use App\Transformers\BaseAPITransformerInterface;
use League\Fractal\TransformerAbstract;

/**
 * Class ShipsListTransformer
 *
 * @package App\Transformers\StarCitizenWiki\Ships
 */
class ShipsListTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
    use FiltersDataTrait;

    protected $validFields = ['wiki_url', 'api_url'];

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

        $transformed = [
            $ship['displaytitle'] => [
                'api_url' => '//'.config('app.api_domain').'/api/v1/ships/'.$ship['displaytitle'],
                'wiki_url' => $ship['fullurl'],
            ],
        ];

        return $this->filterData($transformed);
    }
}