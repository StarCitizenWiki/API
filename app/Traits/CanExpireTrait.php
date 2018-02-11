<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 28.08.2017
 * Time: 19:09
 */

namespace App\Traits;

use Carbon\Carbon;

/**
 * Trait CanExpireTrait
 */
trait CanExpireTrait
{
    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $query
     *
     * @return mixed
     */
    public function scopeNotExpired($query)
    {
        return $query->whereDate('expired_at', '>=', Carbon::now());
    }

    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $query
     *
     * @return mixed
     */
    public function scopeExpired($query)
    {
        return $query->whereDate('expired_at', '<=', Carbon::now());
    }

    /**
     * @return bool
     */
    public function expired(): bool
    {
        return $this->expired_at->lte(Carbon::now());
    }
}
