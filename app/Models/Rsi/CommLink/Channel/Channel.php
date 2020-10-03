<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink\Channel;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Comm-Link Channel
 */
class Channel extends Model
{
    protected $table = 'comm_link_channels';

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * {@inheritdoc}
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @return HasMany
     */
    public function commLinks(): HasMany
    {
        return $this->hasMany(CommLink::class);
    }
}
