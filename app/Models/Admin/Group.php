<?php declare(strict_types = 1);

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Groups
 */
class Group extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function admins()
    {
        return $this->belongsToMany('App\Models\Admin\Admin', 'admin_groups')->withTimestamps();
    }
}
