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
 */
class SystemListTransformer extends AbstractBaseTransformer
{
    protected $validFields = [
        'wiki_url',
        'api_url',
    ];

    /**
     * Transforms the whole ship list
     *
     * @param \App\Models\Starmap\Starsystem $system
     *
     * @return array
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function transform($system)
    {
        $transformed = [
            $system['code'] => [
                'api_url'  => config('app.api_url').'/api/v1/starmap/systems/'.$system['code'],
                'wiki_url' => config('api.wiki_url').'/'.ucfirst(strtolower($system['code'])),
            ],
        ];

        return $this->filterData($transformed);
    }
}
