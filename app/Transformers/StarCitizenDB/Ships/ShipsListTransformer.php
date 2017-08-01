<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 04.03.2017
 * Time: 12:28
 */

namespace App\Transformers\StarCitizenDB\Ships;

use App\Traits\FiltersDataTrait;
use App\Transformers\BaseAPITransformerInterface;
use Illuminate\Support\Facades\File;
use League\Fractal\TransformerAbstract;

/**
 * Class ShipsListTransformer
 *
 * @package App\Transformers\StarCitizenWiki\Ships
 */
class ShipsListTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
    use FiltersDataTrait;

    protected $validFields = [];

    /**
     * Transformes the whole ship list
     *
     * @param mixed $ship Data
     *
     * @return array
     */
    public function transform($ship)
    {
        $content = (string) File::get($ship->getLinkTarget());
        $content = json_decode($content, true);

        $name = $this->prepareName($content['name']);

        $transformed = [
            $name => [
                'api_url' => config('app.api_url').'/api/v1/ships/scdb/'.str_replace(
                    '.json',
                    '',
                    $content['filename']
                ),
            ],
        ];

        return $this->filterData($transformed);
    }

    /**
     * Normalises the Name to match the wiki sites name
     *
     * @param string $name
     *
     * @return String
     */
    private function prepareName(string $name): String
    {
        $name = explode('_', $name);
        unset($name[0]);
        $name = implode('_', $name);

        return $name;
    }
}
