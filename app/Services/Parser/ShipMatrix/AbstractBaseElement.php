<?php

declare(strict_types=1);

namespace App\Services\Parser\ShipMatrix;

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

    /**
     * Decodes HTML Entities and trims the string
     *
     * @param string|null $string
     *
     * @return string
     */
    protected function normalizeString(?string $string): string
    {
        return trim(html_entity_decode($string ?? ''));
    }
}
