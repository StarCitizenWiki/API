<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 13.07.2018
 * Time: 13:34
 */

namespace App\Traits;

/**
 * Trait HasTranslationsTrait
 */
trait HasTranslationsTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function english()
    {
        return $this->translations()->english()->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function german()
    {
        return $this->translations()->german()->first();
    }
}
