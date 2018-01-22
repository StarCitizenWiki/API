<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 29.08.2017
 * Time: 21:57
 */

namespace App\Traits;

use Carbon\Carbon;

/**
 * Trait CanBePulishedTrait
 * @package App\Traits
 */
trait CanBePublishedTrait
{
    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopePublished($query)
    {
        return $query->whereDate('published_at', '<=', Carbon::now());
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeNotPublished($query)
    {
        return $query->whereDate('published_at', '>=', Carbon::now());
    }
}
