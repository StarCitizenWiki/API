<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink;

use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Translatable\HasTranslations;

/**
 * Comm-Link
 */
class CommLink extends Model
{
    use HasFactory;
    use HasTranslations;

    public array $translatable = ['translation'];

    protected $fillable = [
        'cig_id',
        'title',
        'comment_count',
        'images_count',
        'links_count',
        'url',
        'file',
        'channel_id',
        'category_id',
        'series_id',
        'created_at',
        'created_at_file',
        'translation',
    ];

    protected $casts = [
        'cig_id' => 'int',
        'comment_count' => 'int',
        'images_count' => 'int',
        'links_count' => 'int',
        'created_at_file' => 'datetime',
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
     * Previous Comm-Link ID
     *
     * @return Builder|Model|object|null
     *
     * @deprecated Use withNavigationScope for better performance
     */
    protected function getPrevIdAttribute(): ?int
    {
        return CommLink::query()
            ->where('cig_id', '<', $this->cig_id)
            ->orderBy('cig_id', 'desc')
            ->value('cig_id');
    }

    /**
     * Previous Comm-Link
     *
     * @return Builder|Model|object|null
     *
     * @deprecated Use prevId attribute instead
     */
    public function getPrevAttribute()
    {
        if ($this->relationLoaded('prev')) {
            return $this->getRelation('prev');
        }

        return CommLink::query()->where('cig_id', '<', $this->cig_id)->orderBy('cig_id', 'desc')->first(['cig_id']);
    }

    /**
     * Next Comm-Link ID
     */
    protected function getNextIdAttribute(): ?int
    {
        return CommLink::query()
            ->where('cig_id', '>', $this->cig_id)
            ->orderBy('cig_id')
            ->value('cig_id');
    }

    /**
     * Next Comm-Link
     *
     * @return Builder|Model|object|null
     *
     * @deprecated Use nextId attribute instead
     */
    public function getNextAttribute()
    {
        if ($this->relationLoaded('next')) {
            return $this->getRelation('next');
        }

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
     * Scope to load prev/next navigation IDs using subqueries
     */
    public function scopeWithNavigation(Builder $query): Builder
    {
        return $query->addSelect([
            'prev_id' => CommLink::query()
                ->select('cig_id')
                ->whereColumn('cig_id', '<', 'comm_links.cig_id')
                ->orderByDesc('cig_id')
                ->limit(1),
            'next_id' => CommLink::query()
                ->select('cig_id')
                ->whereColumn('cig_id', '>', 'comm_links.cig_id')
                ->orderBy('cig_id')
                ->limit(1),
        ]);
    }

    /**
     * Images Collection
     */
    public function images(): BelongsToMany
    {
        return $this
            ->belongsToMany(Image::class, 'comm_link_image', 'comm_link_id', 'comm_link_image_id')
            ->whereNull('comm_link_images.base_image_id');
    }

    /**
     * Links Collection
     */
    public function links(): BelongsToMany
    {
        return $this->belongsToMany(Link::class, 'comm_link_link', 'comm_link_id', 'comm_link_link_id');
    }

    public function getUrlAttribute($url): string
    {
        return $url ?? sprintf('/comm-link/SCW/%d-API', $this->cig_id);
    }
}
