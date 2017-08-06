<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 04.03.2017
 * Time: 12:28
 */

namespace App\Transformers\StarCitizen\Starmap;

use App\Transformers\AbstractBaseTransformer;

/**
 * Class ShipsListTransformer
 *
 * @package App\Transformers\StarCitizenWiki\Ships
 */
class SystemListTransformer extends AbstractBaseTransformer
{
    protected $validFields = [
        'wiki_url',
        'api_url',
    ];

    /**
     * Transformes the whole ship list
     *
     * @param \App\Models\Starsystem $system
     *
     * @return array
     */
    public function transform($system)
    {
        $transformed = [
            $system['code'] => [
                'api_url'  => config('app.api_url').'/api/v1/starmap/systems/'.$system['code'],
                'wiki_url' => '//star-citizen.wiki/'.ucfirst(strtolower($system['code'])),
            ],
        ];

        return $this->filterData($transformed);
    }
}
