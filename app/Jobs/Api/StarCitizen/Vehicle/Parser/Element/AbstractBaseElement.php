<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser\Element;

use Illuminate\Support\Collection;

/**
 * Class AbstractBaseElement
 */
abstract class AbstractBaseElement
{
    protected Collection $rawData;

    /**
     * AbstractBaseElement constructor.
     *
     * @param Collection $rawData
     */
    public function __construct(Collection $rawData)
    {
        $this->rawData = $rawData;
    }
}
