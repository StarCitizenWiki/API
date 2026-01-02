<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink;

use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * Comm-Link
 */
class CommLink extends Model
{
    use HasFactory;

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

    protected $withCount = [
        'images',
        'links',
    ];

    protected $with = [
        'channel',
        'category',
        'series',
    ];

    protected $casts = [
        'cig_id' => 'int',
    ];

    /**
     * Hide Subscriber Comm-Links from anons
     */
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(
            'limit_subscriber',
            static function (Builder $builder) {
                if (! Auth::check()) {
                    $builder->whereRelation('channel', 'name', '!=', 'Subscriber');
                }
            }
        );
    }

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
        return CommLink::query()->where('cig_id', '<', $this->cig_id)->orderBy('cig_id', 'desc')->first(['cig_id']);
    }

    /**
     * Next Comm-Link
     *
     * @return Builder|Model|object|null
     */
    public function getNextAttribute()
    {
        return CommLink::query()->where('cig_id', '>', $this->cig_id)->orderBy('cig_id')->first(['cig_id']);
    }

    /**
     * Channel Model
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Category Model
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Series Model
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    /**
     * Images Collection
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'comm_link_image', 'comm_link_id', 'comm_link_image_id');
    }

    /**
     * Links Collection
     */
    public function links(): BelongsToMany
    {
        return $this->belongsToMany(Link::class, 'comm_link_link', 'comm_link_id', 'comm_link_link_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CommLinkTranslation::class);
    }

    public function getUrlAttribute($url): string
    {
        return $url ?? sprintf('/comm-link/SCW/%d-API', $this->cig_id);
    }
}
