<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.03.2017
 * Time: 20:04
 */

namespace App\Transformers\StarCitizen\Starmap;

use App\Transformers\AbstractBaseTransformer;

/**
 * Class SystemTransformer
 *
 * @package App\Transformers\StarCitizen\Starmap
 */
class SystemTransformer extends AbstractBaseTransformer
{

    const FILTER_FIELDS = ['sourcedata', 'id', 'exclude', 'created_at', 'updated_at', 'info_url'];
    const RENAME_KEYS = ['cig_id' => 'id', 'cig_time_modified' => 'time_modified'];
    const SUBARRAY_NODES = ['position', 'affiliation', 'aggregated'];

    /**
     *
     * @param mixed $system System Data
     *
     * @return mixed
     */
    public function transform($system)
    {
        $system = $this->moveToSubarray($system, static::SUBARRAY_NODES);
        return $this->filterAndRenameFields($system);
    }
}
