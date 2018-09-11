<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.09.2018
 * Time: 17:49
 */

namespace App\Jobs\Rsi\CommLink\Parser\Element;

/**
 * Base Methods for Elements
 */
abstract class AbstractBaseElement
{
    /**
     * Removes all new lines and trims the string
     *
     * @param string $string
     *
     * @return string cleaned text
     */
    protected function cleanText(string $string): string
    {
        return trim(preg_replace('/\R/', '', $string));
    }
}
