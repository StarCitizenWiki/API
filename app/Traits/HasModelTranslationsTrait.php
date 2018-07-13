<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 13.07.2018
 * Time: 13:34
 */

namespace App\Traits;

/**
 * Trait HasModelTranslationsTrait
 */
trait HasModelTranslationsTrait
{
    /**
     * @return mixed
     */
    public function translations()
    {
        return $this->hasMany(self::class.'Translation');
    }
}
