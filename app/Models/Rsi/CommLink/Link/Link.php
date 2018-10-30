<?php declare(strict_types = 1);

namespace App\Models\Rsi\CommLink\Link;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Link
 */
class Link extends Model
{
    protected $table = 'comm_link_links';

    protected $fillable = [
        'href',
        'text',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function commLinks()
    {
        return $this->belongsToMany(CommLink::class);
    }
}
