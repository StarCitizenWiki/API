<?php

declare(strict_types=1);

namespace App\Traits\Jobs;

trait CheckRsiDataStructureTrait
{
    /**
     * Check if Data is successful, and if Data contains the check Array values in is structure
     * e.g. for check ['data, 'resultset'], data hs to contain the key 'data' with an array value,
     * which contains a key with 'resultset'.
     *
     * @param array $data  Checked Array
     * @param array $check List of Keys that are checked
     *
     * @return bool true when all Elements of $check in $data and success = 1, otherwise false
     */
    protected function checkDataStructureIsValid(array $data, array $check): bool
    {
        if (is_array($data) && 1 === $data['success']) {
            return $this->checkArrayStructure($data, $check);
        }

        return false;
    }

    /**
     * Recursive Check of Array Structure.
     *
     * @param array $data  Checked Array
     * @param array $check List of Keys that are checked
     *
     * @return bool true when all Elements of $check in $data, otherwise false
     */
    protected function checkArrayStructure(array $data, array $check): bool
    {
        if (!empty($check) && !empty($data)) {
            if (array_key_exists($check[0], $data)) {
                $checkKey = array_shift($check);

                return $this->checkArrayStructure($data[$checkKey], $check);
            }

            return false;
        }

        return true;
    }
}
