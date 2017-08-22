<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 22.08.2017
 * Time: 20:00
 */

namespace App\Helpers;

use Hashids\Hashids;

/**
 * Class Hasher
 * @package App\Helpers
 */
class Hasher
{
    /**
     * @param array ...$args
     *
     * @return string
     */
    public static function encode(...$args)
    {
        return app(Hashids::class)->encode(...$args);
    }

    /**
     * @param $enc
     *
     * @return mixed
     */
    public static function decode($enc)
    {
        if (is_int($enc)) {
            return $enc;
        }

        return app(Hashids::class)->decode($enc)[0];
    }
}
