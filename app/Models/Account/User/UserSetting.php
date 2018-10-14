<?php declare(strict_types = 1);

namespace App\Models\Account\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminSetting
 */
class UserSetting extends Model
{
    protected $fillable = [
        'receive_comm_link_notifications',
        'receive_api_notifications',
        'no_api_throttle',
    ];

    protected $casts = [
        'receive_comm_link_notifications' => 'boolean',
        'receive_api_notifications' => 'boolean',
        'no_api_throttle' => 'boolean',
    ];

    /**
     * The associated Admin
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeReceiveApiNotifications(Builder $query)
    {
        $query->where('receive_api_notifications', true);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeReceiveCommLinkNotifications(Builder $query)
    {
        $query->where('receive_comm_link_notifications', true);
    }

    /**
     * @return bool
     */
    public function receiveCommLinkNotifications(): bool
    {
        return $this->receive_comm_link_notifications ?? false;
    }

    /**
     * @return bool
     */
    public function receiveApiNotifications(): bool
    {
        return $this->receive_api_notifications ?? false;
    }

    /**
     * @return bool
     */
    public function isUnthrottled(): bool
    {
        return $this->no_api_throttle ?? false;
    }
}
