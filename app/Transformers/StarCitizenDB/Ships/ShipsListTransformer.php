<?php
/**
 * User: Hannes
 * Date: 04.03.2017
 * Time: 12:28
 */

namespace App\Transformers\StarCitizenDB\Ships;

use App\Traits\FiltersDataTrait;
use App\Transformers\BaseAPITransformerInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;

/**
 * Class ShipsListTransformer
 *
 * @package App\Transformers\StarCitizenWiki\Ships
 */
class ShipsListTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
    use FiltersDataTrait;

    protected $validFields = [

    ];

    /**
     * Transformes the whole ship list
     *
     * @param mixed $ship Data
     *
     * @return array
     */
    public function transform($ship)
    {
        $content = (String) File::get($ship->getLinkTarget());
        $content = json_decode($content, true);

        $name = $this->prepareName($content['name']);
        $manufacturerID = explode('_', $content['name']);
        $manufacturerID = strtoupper($manufacturerID[0]);

        $transformed = [
            $name => [
                'api_url' => '//'.config('app.api_url').'/api/v1/ships/scdb/'.str_replace('.json', '', $content['filename']),
            ],
        ];

        return $this->filterData($transformed);
    }

    /**
     * Normalises the Name to match the wiki sites name
     *
     * @param String $name
     *
     * @return String
     */
    private function prepareName(String $name) : String
    {
        $name = explode('_', $name);
        unset($name[0]);
        $name = implode('_', $name);

        return $name;
    }
}