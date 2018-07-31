<?php declare(strict_types = 1);

namespace App\Models\Account\Admin;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Groups
 */
class AdminGroup extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function admins()
    {
        return $this->belongsToMany(Admin::class)->withTimestamps();
    }
}
