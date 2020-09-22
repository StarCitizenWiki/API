<?php

declare(strict_types=1);

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
    public function getRouteKey(): string
    {
        return Hashids::connection(static::class)->encode($this->getKey());
    }
}
