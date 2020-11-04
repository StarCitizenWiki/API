<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink\Link;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Link
 */
class Link extends Model
{
    use HasFactory;

    protected $table = 'comm_link_links';

    protected $fillable = [
        'href',
        'text',
    ];

    /**
     * @return BelongsToMany
     */
    public function commLinks(): BelongsToMany
    {
        return $this->belongsToMany(CommLink::class);
    }
}
