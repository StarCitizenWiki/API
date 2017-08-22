<?php declare(strict_types = 1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Notification
 * @package App\Mo
 */
class Notification extends Model
{
    use SoftDeletes;

    public const NOTIFICATION_LEVEL_TYPES = [
        0 => 'info',
        1 => 'warning',
        2 => 'error',
        3 => 'critical',
    ];

    protected $fillable = [
        'content',
        'expires_at',
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
        'expires_at',
        'published_at',
    ];

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeNotExpired($query)
    {
        return $query->whereDate('expires_at', '>=', Carbon::now());
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeExpired($query)
    {
        return $query->whereDate('expires_at', '<=', Carbon::now());
    }

    /**
     * @return bool
     */
    public function expired(): bool
    {
        return $this->expires_at->lte(Carbon::now());
    }

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
}
