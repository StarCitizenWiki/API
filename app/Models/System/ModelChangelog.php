<?php declare(strict_types = 1);

namespace App\Models\System;

use App\Models\Account\User\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic Model to hold all Changelogs as Json
 */
class ModelChangelog extends Model
{
    protected $fillable = [
        'type',
        'changelog',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'changelog' => 'collection',
    ];

    protected $with = [
        'user',
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
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
