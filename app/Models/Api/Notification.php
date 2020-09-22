<?php

declare(strict_types=1);

namespace App\Models\Api;

use App\Events\ModelUpdating;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use App\Traits\HasObfuscatedRouteKeyTrait as ObfuscatedRouteKey;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Notification
 */
class Notification extends Model
{
    use ObfuscatedRouteKey;
    use ModelChangelog;

    public const NOTIFICATION_LEVEL_TYPES = [
        0 => 'info',
        1 => 'warning',
        2 => 'error',
        3 => 'critical',
    ];

    protected $fillable = [
        'content',
        'expired_at',
        'published_at',
        'level',
        'output_status',
        'output_index',
        'output_email',
        'order',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'expired_at',
        'published_at',
    ];

    protected $casts = [
        'output_status' => 'boolean',
        'output_index' => 'boolean',
        'output_email' => 'boolean',
    ];

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    /**
     * @return string
     */
    public function getBootstrapClass(): string
    {
        switch ($this->level) {
            case 3:
            case 2:
                return 'danger';

            case 1:
                return 'warning';

            case 0:
                return 'info';

            default:
                return '';
        }
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        switch ($this->level) {
            case 3:
            case 2:
                return 'exclamation-triangle';

            case 1:
                return 'exclamation';

            case 0:
                return 'info';

            default:
                return '';
        }
    }

    /**
     * @param Builder $query
     *
     * @return mixed
     */
    public function scopePublished(Builder $query)
    {
        return $query->whereDate('published_at', '<=', Carbon::now());
    }

    /**
     * @param Builder $query
     *
     * @return mixed
     */
    public function scopeNotPublished(Builder $query)
    {
        return $query->whereDate('published_at', '>=', Carbon::now());
    }

    /**
     * @return string
     */
    public function getLevelAsText(): string
    {
        return static::NOTIFICATION_LEVEL_TYPES[$this->level];
    }

    /**
     * @param Builder $query
     *
     * @return mixed
     */
    public function scopeNotExpired(Builder $query)
    {
        return $query->whereDate('expired_at', '>=', Carbon::now());
    }

    /**
     * @param Builder $query
     *
     * @return mixed
     */
    public function scopeExpired(Builder $query)
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

    /**
     * Notifications on the Front Page
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeOnFrontPage(Builder $query): Builder
    {
        return $query->where('output_index', true);
    }

    /**
     * Notifications on the Status Page
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeOnStatusPage(Builder $query): Builder
    {
        return $query->where('output_status', true);
    }

    /**
     * Notifications as Mail
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeAsMail(Builder $query): Builder
    {
        return $query->where('output_email', true);
    }
}
