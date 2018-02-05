<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 13.08.2017 17:57
 */

namespace App\Jobs;

/**
 * Base Class for Download Data Jobs
 * Class AbstractBaseDownloadData
 * @package App\Jobs
 */
abstract class AbstractBaseDownloadData
{

    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzleClient;

    /**
     * Check if Data is successfull, and if Data contains the check Array values in is structure
     * e.g. for check ['data, 'resultset'], data have to contain the key 'data' with an array value,
     * which contains a key with 'resultset'
     *
     * @param $data  array Checked Array
     * @param $check array List of Keys that are checked
     *
     * @return bool true when all Elements of $check in $data and success = 1, otherwise false
     */
    protected function checkIfDataCanBeProcessed($data, $check): bool
    {
        if (is_array($data) && $data['success'] === 1) {
            return $this->checkArrayStructure($data, $check);
        }

        return false;
    }

    /**
     * Recursive Check of Array Structure
     *
     * @param $data  array Checked Array
     * @param $check array List of Keys that are checked
     *
     * @return bool true when all Elements of $check in $data, otherwise false
     */
    protected function checkArrayStructure($data, $check)
    {
        if (!empty($check) && !empty($data)) {
            if (array_key_exists($check[0], $data)) {
                $checkKey = array_shift($check);

                return $this->checkArrayStructure($data[$checkKey], $check);
            } else {
                return false;
            }
        }

        return true;
    }
}
