<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 25.09.2018
 * Time: 12:59
 */

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser\Element;

use Illuminate\Support\Collection;

/**
 * Class AbstractBaseElement
 */
abstract class AbstractBaseElement
{
    protected $rawData;

    /**
     * AbstractBaseElement constructor.
     *
     * @param \Illuminate\Support\Collection $rawData
     */
    public function __construct(Collection $rawData)
    {
        $this->rawData = $rawData;
    }
}
