<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 27.07.2018
 * Time: 14:07
 */

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Class MultipleVersionsScope
 */
class MultipleVersionsScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model   $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->groupBy(['cig_id'])->havingRaw('created_at = max(created_at)');
    }

    /**
     * Add the all Versions extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return void
     */
    protected function addAllVersions(Builder $builder)
    {
        $builder->macro(
            'allVersions',
            function (Builder $builder) {

                return $builder->withoutGlobalScope($this);
            }
        );
    }
}
