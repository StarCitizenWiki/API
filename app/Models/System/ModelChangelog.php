<?php declare(strict_types = 1);

namespace App\Models\System;

use App\Models\Account\Admin\Admin;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic Model to hold all Changelogs as Json
 */
class ModelChangelog extends Model
{
    protected $fillable = [
        'type',
        'changelog',
        'admin_id',
        'created_at',
    ];

    protected $casts = [
        'changelog' => 'collection',
    ];

    protected $with = [
        'admin',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function changelog()
    {
        return $this->morphTo();
    }

    /**
     * Associated User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
