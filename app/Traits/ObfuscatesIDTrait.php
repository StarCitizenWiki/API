<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 22.08.2017
 * Time: 19:51
 */

namespace App\Traits;

use App\Helpers\Hasher;

/**
 * Trait ObfuscateModelID
 * @package App\Traits
 */
trait ObfuscatesIDTrait
{
    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        return Hasher::encode($this->getKey());
    }
}
