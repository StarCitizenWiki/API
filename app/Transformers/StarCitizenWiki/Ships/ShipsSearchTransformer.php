<?php
/**
 * User: Hannes
 * Date: 04.03.2017
 * Time: 12:28
 */

namespace App\Transformers\StarCitizenWiki\Ships;

use App\Exceptions\InvalidDataException;
use App\Traits\FiltersDataTrait;
use App\Transformers\BaseAPITransformerInterface;
use Illuminate\Support\Facades\App;
use League\Fractal\TransformerAbstract;

/**
 * Class ShipsSearchTransformer
 *
 * @package App\Transformers\StarCitizenWiki\Ships
 */
class ShipsSearchTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
    use FiltersDataTrait;

    protected $validFields = [
        'wiki_url',
        'api_url',
    ];

    /**
     * Transformes a ship search query
     *
     * @param mixed $search Ship search data
     *
     * @return array
     *
     * @throws InvalidDataException
     */
    public function transform($search)
    {
        $search['title'] = str_replace(' ', '_', $search['title']);
        $result = explode('/', $search['title']);
        if (count($result) === 3) {
            $shipName = $result[2];

            $data = [
                $shipName => [
                    'api_url' => '//'.config('app.api_url').'/api/v1/ships/'.$shipName,
                    'wiki_url' => '//star-citizen.wiki/'.$search['title'],
                ],
            ];

            return $this->filterData($data);
        }

        App::make('Log')::warning('Invalid Ship Search Result. Size should be 3, is '.count($result), [
            'search' => $search,
        ]);

        throw new InvalidDataException(
            'result size should be 3, is '.count($result)
        );
    }
}