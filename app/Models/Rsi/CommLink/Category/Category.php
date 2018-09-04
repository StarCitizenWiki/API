<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 30.08.2018
 * Time: 10:24
 */

namespace App\Models\Rsi\CommLink\Category;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Database\Eloquent\Model;

/**
 * Comm Link Category
 */
class Category extends Model
{
    protected $table = 'comm_link_categories';

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
