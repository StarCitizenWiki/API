<?php declare(strict_types = 1);

namespace App\Models\Rsi\CommLink;

use Illuminate\Database\Eloquent\Model;

/**
 * Simple Model to hold changed Comm Links
 */
class CommLinkChanged extends Model
{
    protected $table = 'comm_link_changed';

    protected $fillable = [
        'comm_link_id',
        'had_content',
        'type',
    ];

    protected $with = [
        'commLink',
    ];

    /**
     * The Associated Comm Link
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function commLink()
    {
        return $this->belongsTo(CommLink::class);
    }
}
