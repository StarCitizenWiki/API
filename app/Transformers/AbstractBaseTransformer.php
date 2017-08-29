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
 * @package App\Transformers
 */
abstract class AbstractBaseTransformer extends TransformerAbstract
{
    protected $filters = [];
    protected $requestedFields = [];
    protected $validFields = [];

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
}
