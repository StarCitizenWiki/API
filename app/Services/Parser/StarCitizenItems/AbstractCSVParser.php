<?php


namespace App\Services\Parser\StarCitizenItems;


abstract class AbstractCSVParser
{
    const IDX_VERSION = 0;
    const IDX_NAME = 1;
    const IDX_CATEGORY = 2;

    const IDX_MERCHANT = 5;
    const IDX_PLACE = 6;
    const IDX_PRICE_MIN = 7;

    const IDX_RENTAL_MERCHANT = 21;
    const IDX_RENTAL_PLACE = 22;
    const IDX_RENTAL_PRICE_START = 23;
    const IDX_REC_PRICE = 27;
}
