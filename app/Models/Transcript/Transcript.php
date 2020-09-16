<?php

declare(strict_types=1);

namespace App\Models\Transcript;

use App\Events\ModelUpdating;
use App\Models\Rsi\Video\VideoFormat;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'wiki_id',
        'title',
        'youtube_url',
        'format_id',

        'source_title',
        'source_url',
        'source_published_at',
        'published_at',
    ];

    protected $with = [
        'translations',
    ];

    protected $casts = [
        'wiki_id' => 'int',
        'format_id' => 'int',
        'published_at' => 'datetime',
        'source_published_at' => 'datetime',
    ];

    /**
     * @return HasMany
     */
    public function translations()
    {
        return $this->hasMany(TranscriptTranslation::class);
    }

    /**
     * @return BelongsTo
     */
    public function format(): BelongsTo
    {
        return $this->belongsTo(VideoFormat::class);
    }

    /**
     * Previous Transcript.
     *
     * @return Builder|Model|object|null
     */
    public function getPrevAttribute()
    {
        return Transcript::query()->where('id', '<', $this->id)->orderBy('id', 'desc')->first(['id']);
    }

    /**
     * Next Transcript.
     *
     * @return Builder|Model|object|null
     */
    public function getNextAttribute()
    {
        return Transcript::query()->where('id', '>', $this->id)->orderBy('id')->first(['id']);
    }
}
