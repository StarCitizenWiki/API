<?php declare(strict_types = 1);

namespace App\Models\Rsi\CommLink;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Simple Model to hold changed Comm-Links
 */
class CommLinksChanged extends Model
{
    protected $table = 'comm_links_changed';

    protected $fillable = [
        'comm_link_id',
        'had_content',
        'type',
    ];

    protected $with = [
        'commLink',
    ];

    /**
     * The Associated Comm-Link
     *
     * @return BelongsTo
     */
    public function commLink(): BelongsTo
    {
        return $this->belongsTo(CommLink::class);
    }
}
