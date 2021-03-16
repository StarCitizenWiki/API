<?php

declare(strict_types=1);

namespace App\Services\Gdoc\Parser;

/**
 * Contains indices found in the 'Preise' csv table
 */
abstract class PriceTableCSVParser extends AbstractCSVParser
{
    public const IDX_VERSION = 0;
    public const IDX_NAME = 1;
    public const IDX_CATEGORY = 2;

    public const IDX_MERCHANT = 5;
    public const IDX_PLACE = 6;
    public const IDX_PRICE_MIN = 7;
    public const IDX_PRICE_MAX = 8;
    public const IDX_AMOUNT_TYPE = 9;
    public const IDX_PRICE_TYPE = 10;

    public const IDX_SELLABLE = 12;

    public const IDX_RENTAL_MERCHANT = 21;
    public const IDX_RENTAL_PLACE = 22;
    public const IDX_RENTAL_PRICE_START = 23;
    public const IDX_REC_PRICE = 27;

    /**
     * @var false|resource
     */
    protected $fileHandle;
}
