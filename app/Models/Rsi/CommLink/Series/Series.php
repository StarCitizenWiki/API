<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 30.08.2018
 * Time: 10:25
 */

namespace App\Models\Rsi\CommLink\Series;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Database\Eloquent\Model;

/**
 * Series Model
 */
class Series extends Model
{
    protected $table = 'comm_link_series';

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function commLinks()
    {
        return $this->hasMany(CommLink::class);
    }

    /**
     * Name as slug
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        return $this->slug;
    }
}
