<?php

declare(strict_types=1);

namespace App\Models\Account\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AdminSetting
 */
class UserSetting extends Model
{
    protected $fillable = [
        'receive_comm_link_notifications',
        'receive_api_notifications',
        'no_api_throttle',
        'language'
    ];

    protected $casts = [
        'receive_comm_link_notifications' => 'boolean',
        'receive_api_notifications' => 'boolean',
        'no_api_throttle' => 'boolean',
    ];

    /**
     * The associated Admin
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param Builder $query
     */
    public function scopeReceiveApiNotifications(Builder $query): void
    {
        $query->where('receive_api_notifications', true);
    }

    /**
     * @param Builder $query
     */
    public function scopeReceiveCommLinkNotifications(Builder $query): void
    {
        $query->where('receive_comm_link_notifications', true);
    }

    /**
     * @return bool
     */
    public function isUnthrottled(): bool
    {
        return $this->no_api_throttle ?? false;
    }
}
