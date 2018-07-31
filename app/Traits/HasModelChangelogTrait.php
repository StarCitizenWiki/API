<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 31.07.2018
 * Time: 16:32
 */

namespace App\Traits;

/**
 * Trait HasModelChangelogTrait
 */
trait HasModelChangelogTrait
{
    /**
     * Changelogs
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function changelogs()
    {
        return $this->morphMany('App\Models\Api\ModelChangelog', 'changelog');
    }
}
