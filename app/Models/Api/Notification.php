<?php declare(strict_types = 1);

namespace App\Models\Api;

use App\Traits\ObfuscatesIDTrait as ObfuscatesID;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Notification
 */
class Notification extends Model
{
    use SoftDeletes;
    use ObfuscatesID;

    public const NOTIFICATION_LEVEL_TYPES = [
        -1 => 'no notifications',
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
     * @param \Doctrine\DBAL\Query\QueryBuilder $query
     *
     * @return mixed
     */
    public function scopePublished($query)
    {
        return $query->whereDate('published_at', '<=', Carbon::now());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return mixed
     */
    public function scopeNotExpired(Builder $query)
    {
        return $query->whereDate('expired_at', '>=', Carbon::now());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnFrontPage(Builder $query)
    {
        return $query->where('output_index', true);
    }

    /**
     * Notifications on the Status Page
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnStatusPage(Builder $query)
    {
        return $query->where('output_status', true);
    }

    /**
     * Notifications as Mail
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAsMail(Builder $query)
    {
        return $query->where('output_email', true);
    }
}
