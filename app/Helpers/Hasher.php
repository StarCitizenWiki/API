<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 22.08.2017
 * Time: 20:00
 */

namespace App\Helpers;

use Hashids\Hashids;
use Hashids\HashidsException;

/**
 * Class Hasher
 * Einfacher Wrapper für HashIds
 */
class Hasher
{
    /**
     * @param array ...$args
     *
     * @return string
     */
    public static function encode(...$args): string
    {
        return app(Hashids::class)->encode(...$args);
    }

    /**
     * @param mixed $enc Data to encode
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *
     * @return int
     */
    public static function decode($enc): int
    {
        if (is_int($enc)) {
            return $enc;
        }

        $decoded = app(Hashids::class)->decode($enc);

        if (empty($decoded) || !is_integer($decoded[0])) {
            throw new HashidsException();
        }

        return $decoded[0];
    }
}
