<?php declare(strict_types = 1);

namespace App\Models\System;

use App\Models\Account\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
     * @return MorphTo
     */
    public function changelog(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Associated User
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
