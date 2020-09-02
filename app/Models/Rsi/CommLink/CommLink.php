<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 30.08.2018
 * Time: 10:22
 */

namespace App\Models\Rsi\CommLink;

use App\Events\ModelUpdating;
use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Link\Link;
use App\Models\Rsi\CommLink\Series\Series;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Comm-Link
 */
class CommLink extends HasTranslations
{
    use ModelChangelog;

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $fillable = [
        'cig_id',
        'title',
        'comment_count',
        'url',
        'file',
        'channel_id',
        'category_id',
        'series_id',
        'created_at',
    ];

    protected $with = [
        'channel',
        'category',
        'series',
        'images',
        'links',
        'translations',
    ];

    protected $withCount = [
        'images',
        'links',
    ];

    protected $casts = [
        'cig_id' => 'int',
    ];

    /**
     * {@inheritdoc}
     */
    public function getRouteKeyName(): string
    {
        return 'cig_id';
    }

    /**
     * Previous Comm-Link
     *
     * @return Builder|Model|object|null
     */
    public function getPrevAttribute()
    {
        return CommLink::query()->where('id', '<', $this->id)->orderBy('id', 'desc')->first(['cig_id']);

    }

    /**
     * Next Comm-Link
     *
     * @return Builder|Model|object|null
     */
    public function getNextAttribute()
    {
        return CommLink::query()->where('id', '>', $this->id)->orderBy('id')->first(['cig_id']);
    }

    /**
     * Channel Model
     *
     * @return BelongsTo
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Category Model
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Series Model
     *
     * @return BelongsTo
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    /**
     * Images Collection
     *
     * @return BelongsToMany
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'comm_link_image', 'comm_link_id', 'comm_link_image_id');
    }

    /**
     * Links Collection
     *
     * @return BelongsToMany
     */
    public function links(): BelongsToMany
    {
        return $this->belongsToMany(Link::class, 'comm_link_link', 'comm_link_id', 'comm_link_link_id');
    }

    /**
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CommLinkTranslation::class);
    }

    /**
     * @return HasManyThrough
     */
    public function translationChangelogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\System\ModelChangelog::class,
            CommLinkTranslation::class,
            'comm_link_id',
            'changelog_id'
        )->where('changelog_type', CommLinkTranslation::class);
    }
}
