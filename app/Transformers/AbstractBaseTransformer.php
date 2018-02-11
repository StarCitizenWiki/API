<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 02.08.2017
 * Time: 13:41
 */

namespace App\Transformers;

use App\Exceptions\InvalidDataException;
use Illuminate\Http\Request;
use League\Fractal\TransformerAbstract;

/**
 * Class AbstractBaseTransformer
 */
abstract class AbstractBaseTransformer extends TransformerAbstract
{
    protected $filters = [];
    protected $requestedFields = [];
    protected $validFields = [];

    /**
     * Override in Child, using keys that should be filtered
     */
    const FILTER_FIELDS = [];

    /**
     * Override in Child, using key as original key and value as new key name
     * e.g. 'cig_id' => 'id' replace the key name 'cig_id' to 'id'
     */
    const RENAME_KEYS = [];

    /**
     * Adds requested fields to the filter array
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function addFilters(Request $request)
    {
        $filters = $request->get('fields', null);
        if (!is_null($filters) && !empty($filters)) {
            $this->requestedFields = explode(',', $filters);
            $this->validateRequestedFields();
        }
    }

    /**
     * @param array $data
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function addFilterArray(array $data): void
    {
        $this->requestedFields = $data;
        $this->validateRequestedFields();
    }

    /**
     * Filters data from a one dimensional array
     *
     * @param array $data
     *
     * @return array
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function filterData(array &$data)
    {
        if (empty($this->filters)) {
            return $data;
        }

        if (!is_array($data)) {
            throw new InvalidDataException('Can only filter arrays');
        }

        foreach ($data as $key => $item) {
            if (is_array($item)) {
                $data[$key] = $this->filterData($item);
            }
            if (in_array($key, $this->validFields) && !in_array($key, $this->filters)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getAvailableFields(): array
    {
        if (isset($this->validFields)) {
            return $this->validFields;
        }

        return [];
    }

    /**
     * @throws \App\Exceptions\InvalidDataException
     */
    protected function validateRequestedFields()
    {
        foreach ($this->requestedFields as $field) {
            if (!in_array($field, $this->validFields)) {
                throw new InvalidDataException(
                    'Requested field '.$field.' is not in valid fields ['.implode(',', $this->validFields).']'
                );
            }
            $this->filters[] = $field;
        }
    }

    /**
     * When $array Keys start with Element of $newNodes, Key is moved to a new Subarray
     * e.g. $array['entry_status'] is moved to $array['entry']['status']
     *
     * @param array $array
     * @param array $newNodes array of new search and to moved Keys
     *
     * @return mixed
     */
    protected function moveToSubarray($array, $newNodes)
    {
        foreach ($array as $key => $value) {
            foreach ($newNodes as $newNode) {
                if (substr($key, 0, strlen($newNode)) === $newNode) {
                    $newKey = substr($key, strlen($newNode) + 1, strlen($key));
                    $array[$newNode][$newKey] = $value;
                    unset($array[$key]);
                }
            }
        }

        return $array;
    }

    /**
     * Filter FILTER_FIELDS from $array and Rename $array Keys from RENAME_KEYS Key to RENAME_KEYS value
     * Recursive call, if $array contains another array
     *
     * @param array $array
     *
     * @return array Filtered and Renamed Array
     */
    protected function filterAndRenameFields($array)
    {
        // Filter By Key
        $filteredArray = array_filter(
            $array,
            function ($key) {
                return !in_array($key, static::FILTER_FIELDS);
            },
            ARRAY_FILTER_USE_KEY
        );

        // Renaming of Keys
        $filteredAndRenamedArray = [];
        foreach ($filteredArray as $filteredKey => $filteredValue) {
            if (array_key_exists($filteredKey, static::RENAME_KEYS)) {
                $filteredAndRenamedArray[static::RENAME_KEYS[$filteredKey]] = $filteredValue;
            } else {
                $filteredAndRenamedArray[$filteredKey] = $filteredValue;
            }
        }

        // Recursive Call for Subarrays
        foreach ($filteredAndRenamedArray as $filteredAndRenamedArrayKey => $filteredAndRenamedArrayValue) {
            if (is_array($filteredAndRenamedArrayValue)) {
                $filteredAndRenamedArray[$filteredAndRenamedArrayKey] = $this->filterAndRenameFields(
                    $filteredAndRenamedArrayValue
                );
            }
        }

        return $filteredAndRenamedArray;
    }
}
