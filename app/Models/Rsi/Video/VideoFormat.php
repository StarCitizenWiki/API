<?php

declare(strict_types=1);

namespace App\Models\Rsi\Video;

use App\Models\Transcript\Transcript;
use Illuminate\Database\Eloquent\Model;

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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transcripts()
    {
        return $this->hasMany(Transcript::class);
    }
}
