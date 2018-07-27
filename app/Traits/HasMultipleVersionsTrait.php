<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 27.07.2018
 * Time: 14:33
 */

namespace App\Traits;

use App\Scopes\MultipleVersionsScope;

/**
 * Adds the MultipleVersionsScope to the Model instance
 */
trait HasMultipleVersionsTrait
{
    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootHasMultipleVersionsTrait()
    {
        static::addGlobalScope(new MultipleVersionsScope());
    }
}
