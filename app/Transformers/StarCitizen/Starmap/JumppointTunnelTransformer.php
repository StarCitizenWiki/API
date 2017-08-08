<?php
/**
 * User: Keonie
 * Date: 03.08.2017 16:42
 */

namespace App\Transformers\StarCitizen\Starmap;

use App\Transformers\AbstractBaseTransformer;

/**
 * Class JumppointTunnelTransformer
 * @package App\Transformers\StarCitizen\Starmap
 */
class JumppointTunnelTransformer extends AbstractBaseTransformer
{
    const FILTER_FIELDS = ['sourcedata', 'id', 'exclude', 'created_at', 'updated_at'];
    const RENAME_KEYS = ['cig_id' => 'id', 'cig_system_id' => 'system_id'];
    const SUBARRAY_NODES = ['entry', 'exit'];

    /**
     * @param $jumppointTunnel
     *
     * @return mixed
     */
    public function transform($jumppointTunnel)
    {
        $jumppointTunnel = $this->moveToSubarray($jumppointTunnel, self::SUBARRAY_NODES);
        return $this->filterAndRenameFields($jumppointTunnel);
    }

    /**
     * When $array Keys start with Element of $newNodes, Key is moved to a new Subarray
     * e.g. $array['entry_status'] is moved to $array['entry']['status']
     * @param $array
     * @param $newNodes array of new search and to moved Keys
     *
     * @return mixed
     */
    private function moveToSubarray($array, $newNodes)
    {
        foreach ($array as $key => $value) {
            foreach ($newNodes as $newNode) {
                if (substr($key, 0, strlen($newNode)) === $newNode) {
                    $newKey = substr($key, strlen($newNode)+1, strlen($key));
                    $array[$newNode][$newKey] = $value;
                    unset($array[$key]);
                }
            }
        }
        return $array;
    }

    /**
     * Filter FILTER_FIELDS from $array and Rename $array Keys from RENAME_KEYS Key to RENAME_KEYS value
     * Recursiv call, if $array contains another array
     * @param $array array
     *
     * @return array Filtered and Renamed Array
     */
    private function filterAndRenameFields($array)
    {
        // Filter By Key
        $filteredArray = array_filter(
            $array,
            function ($key) {
                return !in_array($key, self::FILTER_FIELDS);
            },
            ARRAY_FILTER_USE_KEY
        );

        // Renaming of Keys
        $filteredAndRenamedArray = [];
        foreach ($filteredArray as $filteredKey => $filteredValue) {
            if (array_key_exists($filteredKey, self::RENAME_KEYS)) {
                $filteredAndRenamedArray[self::RENAME_KEYS[$filteredKey]] = $filteredValue;
            } else {
                $filteredAndRenamedArray[$filteredKey] = $filteredValue;
            }
        }

        // Recursive Call for Subarrays
        foreach ($filteredAndRenamedArray as $filteredAndRenamedArrayKey => $filteredAndRenamedArrayValue) {
            if (is_array($filteredAndRenamedArrayValue)) {
                $filteredAndRenamedArray[$filteredAndRenamedArrayKey] = $this->filterAndRenameFields($filteredAndRenamedArrayValue);
            }
        }
        
        return $filteredAndRenamedArray;
    }
}