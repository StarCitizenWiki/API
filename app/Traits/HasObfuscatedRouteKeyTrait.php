<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 09.08.2018
 * Time: 12:27
 */

namespace App\Traits;

use Vinkla\Hashids\Facades\Hashids;

/**
 * Simple Model Trait that encodes the primary key
 */
trait HasObfuscatedRouteKeyTrait
{
    /**
     * Encodes the primary key with HashIDs
     *
     * @return string
     */
    public function getRouteKey()
    {
        return Hashids::connection(static::class)->encode($this->getKey());
    }
}
