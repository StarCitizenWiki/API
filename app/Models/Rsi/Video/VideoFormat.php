<?php

declare(strict_types=1);

namespace App\Models\Rsi\Video;

use App\Models\Transcript\Transcript;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoFormat extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * {@inheritdoc}
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return HasMany
     */
    public function transcripts(): HasMany
    {
        return $this->hasMany(Transcript::class);
    }
}
