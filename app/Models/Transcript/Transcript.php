<?php

declare(strict_types=1);

namespace App\Models\Transcript;

use App\Events\ModelUpdating;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transcript extends HasTranslations
{
    use ModelChangelog;

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $fillable = [
        'title',
        'youtube_id',
        'playlist_name',
        'upload_date',
        'runtime',
        'thumbnail',
        'youtube_description',
        'filename',
    ];

    protected $with = [
        'translations',
    ];

    protected $casts = [
        'upload_date' => 'date',
        'runtime' => 'int',
    ];

    public function getRouteKeyName()
    {
        return 'youtube_id';
    }

    /**
     * @return HasMany
     */
    public function translations()
    {
        return $this->hasMany(TranscriptTranslation::class);
    }

    /**
     * Previous Transcript.
     *
     * @return Builder|Model|object|null
     */
    public function getPrevAttribute()
    {
        return self::query()
            ->where('id', '<', $this->id)
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first(['youtube_id', 'title']);
    }

    /**
     * Next Transcript.
     *
     * @return Builder|Model|object|null
     */
    public function getNextAttribute()
    {
        return self::query()
            ->where('id', '>', $this->id)
            ->orderBy('id')
            ->limit(1)
            ->first(['youtube_id', 'title']);
    }

    public function getYoutubeUrlAttribute()
    {
        return sprintf('https://www.youtube.com/watch?v=%s', $this->youtube_id);
    }
}
